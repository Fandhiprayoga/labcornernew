<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\LaboratoryLaboranModel;
use App\Models\LaboratoryModel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;

class AssetController extends BaseController
{
    protected AssetModel $assetModel;
    protected LaboratoryLaboranModel $laboratoryLaboranModel;
    protected LaboratoryModel $laboratoryModel;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];
    private const MAX_BULK_ROWS = 500;
    private const BULK_IMPORT_SESSION_TTL = 1800;

    public function __construct()
    {
        $this->assetModel = new AssetModel();
        $this->laboratoryLaboranModel = new LaboratoryLaboranModel();
        $this->laboratoryModel = new LaboratoryModel();
    }

    public function index()
    {
        $filters = $this->assetFilters();
        $search = $filters['search'];
        $laboratoryId = $filters['laboratoryId'];
        $status = $filters['status'];
        $borrowable = $filters['borrowable'];
        $perPage       = (int) $this->request->getGet('perPage');

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $assets = $this->assetQuery($filters)->paginate($perPage);
        $pager = $this->assetModel->pager;

        return $this->renderView('assets/index', [
            'title' => 'Master Asset',
            'page_title' => 'Master Asset',
            'assets' => $assets,
            'pager' => $pager,
            'search' => $search,
            'laboratoryId' => $laboratoryId,
            'status' => $status,
            'borrowable' => $borrowable,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $pager->getCurrentPage(),
            'totalRows' => $pager->getTotal(),
            'laboratoryOptions' => $this->laboratoryOptions(),
            'statuses' => AssetModel::statuses(),
            'statusBadges' => AssetModel::statusBadges(),
        ]);
    }

    public function exportCsv()
    {
        $assets = $this->assetQuery($this->assetFilters())->findAll();
        $statuses = AssetModel::statuses();

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Kode Asset', 'Nama Asset', 'Laboratorium', 'Kode Ruangan', 'Nama Ruangan', 'Kategori', 'Merek', 'Model/Tipe', 'Nomor Seri', 'Tanggal Perolehan', 'Harga Perolehan', 'Boleh Dipinjam', 'Status', 'Keterangan']);

        foreach ($assets as $asset) {
            fputcsv($stream, [
                $asset['asset_code'],
                $asset['name'],
                $asset['laboratory_name'],
                $asset['room_code'],
                $asset['room_name'],
                $asset['category'],
                $asset['brand'],
                $asset['model'],
                $asset['serial_number'],
                $asset['acquisition_date'],
                $asset['purchase_price'],
                (int) $asset['can_be_borrowed'] === 1 ? 'Ya' : 'Tidak',
                $statuses[$asset['status']] ?? $asset['status'],
                $asset['description'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->download('master-asset-' . date('Y-m-d') . '.csv', $csv)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function printQrLabels()
    {
        $requestedUuids = $this->request->getGet('asset_uuids');
        $requestedUuids = is_array($requestedUuids) ? $requestedUuids : [];
        $uuids = array_values(array_unique(array_filter(
            array_map('strval', $requestedUuids),
            static fn (string $uuid): bool => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $uuid) === 1
        )));

        if (empty($uuids)) {
            return redirect()->to('/admin/assets')->with('error', 'Pilih setidaknya satu asset untuk dicetak labelnya.');
        }

        $assets = $this->assetQuery([
            'search' => '',
            'laboratoryId' => 0,
            'status' => '',
            'borrowable' => '',
        ])->whereIn('assets.uuid', array_slice($uuids, 0, 100))->findAll();

        if (empty($assets)) {
            return redirect()->to('/admin/assets')->with('error', 'Asset yang dipilih tidak ditemukan atau tidak dapat diakses.');
        }

        $writer = new SvgWriter();
        foreach ($assets as &$asset) {
            $asset['qr_code'] = $writer->write(new QrCode(data: $asset['asset_code'], size: 360, margin: 12))->getDataUri();
        }
        unset($asset);

        return $this->renderView('assets/print_qr_labels', [
            'title' => 'Cetak Label QR Asset',
            'assets' => $assets,
        ], 'layouts/print');
    }

    public function create()
    {
        return $this->renderForm('Tambah Asset', 'Tambah Asset');
    }

    public function store()
    {
        if (! $this->isValidSubmission()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->assetModel->insert($this->assetData());

        return redirect()->to('/admin/assets')->with('success', 'Asset berhasil ditambahkan.');
    }

    public function bulkCreate()
    {
        return $this->renderView('assets/bulk_create', [
            'title' => 'Bulk Insert Asset',
            'page_title' => 'Bulk Insert Asset',
            'laboratories' => $this->laboratoryOptions(),
            'statuses' => AssetModel::statuses(),
        ]);
    }

    public function bulkTemplate()
    {
        $laboratoryOptions = $this->laboratoryOptions();
        $sampleLaboratory = $laboratoryOptions[0] ?? null;

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['asset_code', 'name', 'laboratory_id', 'category', 'brand', 'model', 'serial_number', 'acquisition_date', 'purchase_price', 'can_be_borrowed', 'status', 'description']);
        fputcsv($stream, ['AST-CONTOH-001', 'Contoh Nama Asset', $sampleLaboratory['id'] ?? '', 'Peralatan Praktikum', 'Merek', 'Model', 'SN-001', '2024-01-01', '1000000', '1', 'ready', 'Keterangan opsional']);
        fputcsv($stream, []);
        fputcsv($stream, ['# Referensi laboratory_id yang dapat Anda gunakan:']);

        foreach ($laboratoryOptions as $laboratory) {
            fputcsv($stream, ['#', $laboratory['id'], $laboratory['name']]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->download('template-bulk-asset.csv', $csv)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function bulkStore()
    {
        $file = $this->request->getFile('file');

        if (! $file || ! $file->isValid() || $file->getError() !== UPLOAD_ERR_OK) {
            return redirect()->back()->with('error', 'File CSV wajib diunggah.');
        }

        if (strtolower($file->getClientExtension()) !== 'csv' || $file->getSize() > 2 * 1024 * 1024) {
            return redirect()->back()->with('error', 'File harus berformat CSV dengan ukuran maksimal 2 MB.');
        }

        $handle = fopen($file->getTempName(), 'r');

        if ($handle === false) {
            return redirect()->back()->with('error', 'File tidak dapat dibaca.');
        }

        $firstLine = fgets($handle);

        if ($firstLine === false) {
            fclose($handle);

            return redirect()->back()->with('error', 'File CSV kosong.');
        }

        $delimiter = $this->detectCsvDelimiter($firstLine);
        rewind($handle);

        $header = fgetcsv($handle, 0, $delimiter);

        if ($header === false) {
            fclose($handle);

            return redirect()->back()->with('error', 'File CSV kosong.');
        }

        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header[0]);
        $header = array_map(static fn ($column) => strtolower(trim((string) $column)), $header);

        $requiredColumns = ['asset_code', 'name', 'laboratory_id', 'can_be_borrowed', 'status'];
        $missingColumns = array_diff($requiredColumns, $header);

        if (! empty($missingColumns)) {
            fclose($handle);

            return redirect()->back()->with('error', 'Kolom wajib tidak ditemukan: ' . implode(', ', $missingColumns));
        }

        $forcedLaboratoryId = $this->forcedLaboratoryIdForLaboran();
        $rawRows = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if (count(array_filter($row, static fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            // Baris komentar referensi laboratory_id pada template diawali '#', bukan data asset.
            if (str_starts_with(trim((string) $row[0]), '#')) {
                continue;
            }

            if (count($rawRows) >= self::MAX_BULK_ROWS) {
                fclose($handle);

                return redirect()->back()->with('error', 'Maksimal ' . self::MAX_BULK_ROWS . ' baris data per import.');
            }

            $row = array_pad($row, count($header), '');
            $rawRows[$rowNumber] = array_combine($header, $row);
        }

        fclose($handle);

        if (empty($rawRows)) {
            return redirect()->back()->with('error', 'File CSV tidak berisi baris data.');
        }

        $previewRows = [];
        $validRawRows = [];
        $codesInFile = [];

        foreach ($rawRows as $rowNumber => $data) {
            [$assetData, $rowErrors] = $this->prepareBulkRow($data, $forcedLaboratoryId, $codesInFile);

            $previewRows[] = [
                'row_number' => $rowNumber,
                'raw' => $data,
                'errors' => $rowErrors,
                'valid' => empty($rowErrors),
            ];

            if (empty($rowErrors)) {
                $codesInFile[] = $assetData['asset_code'];
                $validRawRows[$rowNumber] = $data;
            }
        }

        session()->set('bulk_asset_import', [
            'raw_rows' => $validRawRows,
            'created_at' => time(),
        ]);

        return $this->renderView('assets/bulk_preview', [
            'title' => 'Pratinjau Bulk Insert Asset',
            'page_title' => 'Pratinjau Bulk Insert Asset',
            'previewRows' => $previewRows,
            'validCount' => count($validRawRows),
            'invalidCount' => count($previewRows) - count($validRawRows),
        ]);
    }

    public function bulkConfirm()
    {
        $import = session('bulk_asset_import');

        if (empty($import['raw_rows'])) {
            return redirect()->to('/admin/assets/bulk-create')->with('error', 'Tidak ada data pratinjau yang tersimpan. Silakan unggah ulang file CSV.');
        }

        if (time() - (int) ($import['created_at'] ?? 0) > self::BULK_IMPORT_SESSION_TTL) {
            session()->remove('bulk_asset_import');

            return redirect()->to('/admin/assets/bulk-create')->with('error', 'Sesi pratinjau sudah kedaluwarsa. Silakan unggah ulang file CSV.');
        }

        // Validasi ulang saat konfirmasi untuk mengantisipasi perubahan data sejak pratinjau dibuat.
        $forcedLaboratoryId = $this->forcedLaboratoryIdForLaboran();
        $rowsToInsert = [];
        $errors = [];
        $codesInFile = [];

        foreach ($import['raw_rows'] as $rowNumber => $data) {
            [$assetData, $rowErrors] = $this->prepareBulkRow($data, $forcedLaboratoryId, $codesInFile);

            if (! empty($rowErrors)) {
                $errors[] = "Baris {$rowNumber}: " . implode(' ', $rowErrors);

                continue;
            }

            $codesInFile[] = $assetData['asset_code'];
            $rowsToInsert[$rowNumber] = $assetData;
        }

        session()->remove('bulk_asset_import');

        if (empty($rowsToInsert)) {
            return redirect()->to('/admin/assets/bulk-create')->with('error', 'Tidak ada baris valid tersisa untuk diimpor. Silakan unggah ulang file CSV.')->with('errors', $errors);
        }

        $db = $this->assetModel->db;
        $db->transStart();

        $inserted = 0;
        foreach ($rowsToInsert as $rowNumber => $assetData) {
            try {
                $this->assetModel->insert($assetData);
                $inserted++;
            } catch (\Throwable $e) {
                $errors[] = "Baris {$rowNumber}: gagal disimpan.";
            }
        }

        $db->transComplete();

        if (! $db->transStatus()) {
            return redirect()->to('/admin/assets/bulk-create')->with('error', 'Import dibatalkan karena terjadi kegagalan saat menyimpan data.')->with('errors', $errors);
        }

        $message = "{$inserted} asset berhasil diimpor.";

        if (! empty($errors)) {
            return redirect()->to('/admin/assets')->with('success', $message)->with('errors', $errors);
        }

        return redirect()->to('/admin/assets')->with('success', $message);
    }

    public function bulkCancel()
    {
        session()->remove('bulk_asset_import');

        return redirect()->to('/admin/assets/bulk-create')->with('success', 'Pratinjau import dibatalkan.');
    }

    private function forcedLaboratoryIdForLaboran(): ?int
    {
        if (! activeGroupIs('laboran')) {
            return null;
        }

        $assignedLaboratoryIds = $this->assignedLaboratoryIds();

        // Laboran dengan satu penugasan tidak perlu mengisi laboratory_id secara manual, dan tidak bisa mengubahnya lewat file.
        return count($assignedLaboratoryIds) === 1 ? $assignedLaboratoryIds[0] : null;
    }

    /**
     * Excel di beberapa lokasi (mis. Indonesia) menyimpan CSV dengan pemisah titik koma, bukan koma.
     */
    private function detectCsvDelimiter(string $line): string
    {
        $candidates = [',', ';', "\t"];
        $bestDelimiter = ',';
        $bestCount = 0;

        foreach ($candidates as $delimiter) {
            $count = substr_count($line, $delimiter);

            if ($count > $bestCount) {
                $bestCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    /**
     * Menerima tanggal umum dari file Excel (mis. d/m/Y, d-m-Y) dan menormalkannya ke Y-m-d.
     */
    private function normalizeCsvDate(string $value): string
    {
        if ($value === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $value, $matches) === 1) {
            [, $year, $month, $day] = array_map('intval', $matches);

            return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : $value;
        }

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $value, $matches) === 1) {
            [, $first, $second, $year] = array_map('intval', $matches);

            // Diasumsikan format d/m/Y (umum di Indonesia); bila tidak valid, coba m/d/Y.
            $day = $first;
            $month = $second;

            if (! checkdate($month, $day, $year) && checkdate($first, $second, $year)) {
                $month = $first;
                $day = $second;
            }

            return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : $value;
        }

        return $value;
    }

    /**
     * @return array{0: array<string, mixed>|null, 1: list<string>}
     */
    private function prepareBulkRow(array $data, ?int $forcedLaboratoryId, array $codesInFile): array
    {
        $assetCode = strtoupper(trim((string) ($data['asset_code'] ?? '')));
        $laboratoryId = $forcedLaboratoryId ?? (int) ($data['laboratory_id'] ?? 0);
        $canBeBorrowed = trim((string) ($data['can_be_borrowed'] ?? ''));
        $status = trim((string) ($data['status'] ?? '')) ?: AssetModel::STATUS_READY;
        $purchasePrice = trim((string) ($data['purchase_price'] ?? ''));
        $acquisitionDate = $this->normalizeCsvDate(trim((string) ($data['acquisition_date'] ?? '')));

        $validationData = [
            'asset_code' => $assetCode,
            'name' => trim((string) ($data['name'] ?? '')),
            'laboratory_id' => $laboratoryId,
            'can_be_borrowed' => $canBeBorrowed,
            'status' => $status,
            'purchase_price' => $purchasePrice,
            'acquisition_date' => $acquisitionDate,
        ];

        $rules = [
            'asset_code' => 'required|alpha_numeric_punct|min_length[2]|max_length[50]|is_unique[assets.asset_code]',
            'name' => 'required|min_length[3]|max_length[150]',
            'laboratory_id' => 'required|integer|is_not_unique[laboratories.id]',
            'can_be_borrowed' => 'required|in_list[0,1]',
            'status' => 'required|in_list[' . implode(',', array_keys(AssetModel::statuses())) . ']',
            'purchase_price' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'acquisition_date' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (! $this->validateData($validationData, $rules)) {
            return [null, array_values($this->validator->getErrors())];
        }

        $errors = [];

        if (in_array($assetCode, $codesInFile, true)) {
            $errors[] = 'Kode asset duplikat di dalam file.';
        }

        if (! $this->canAccessLaboratory($laboratoryId)) {
            $errors[] = 'Anda hanya dapat mengimpor asset ke laboratorium yang ditugaskan kepada Anda.';
        } elseif (! $this->laboratoryModel->where('status', 'active')->find($laboratoryId)) {
            $errors[] = 'Laboratorium tidak aktif atau tidak ditemukan.';
        }

        if ($canBeBorrowed === '0' && $status === AssetModel::STATUS_BORROWED) {
            $errors[] = 'Asset yang tidak boleh dipinjam tidak dapat berstatus sedang dipinjam.';
        }

        if (! empty($errors)) {
            return [null, $errors];
        }

        return [[
            'asset_code' => $assetCode,
            'name' => $validationData['name'],
            'laboratory_id' => $laboratoryId,
            'category' => trim((string) ($data['category'] ?? '')),
            'brand' => trim((string) ($data['brand'] ?? '')),
            'model' => trim((string) ($data['model'] ?? '')),
            'serial_number' => trim((string) ($data['serial_number'] ?? '')),
            'acquisition_date' => $acquisitionDate ?: null,
            'purchase_price' => $purchasePrice === '' ? null : $purchasePrice,
            'can_be_borrowed' => (int) $canBeBorrowed,
            'status' => $status,
            'description' => trim((string) ($data['description'] ?? '')),
        ], []];
    }

    public function edit(string $uuid)
    {
        $asset = $this->assetModel->findByUuid($uuid);

        if (! $asset) {
            return redirect()->to('/admin/assets')->with('error', 'Asset tidak ditemukan.');
        }

        if (! $this->canAccessLaboratory((int) $asset['laboratory_id'])) {
            return redirect()->to('/admin/assets')->with('error', 'Anda hanya dapat mengelola asset pada laboratorium yang ditugaskan.');
        }

        return $this->renderForm('Edit Asset', 'Edit Asset', $asset);
    }

    public function update(string $uuid)
    {
        $asset = $this->assetModel->findByUuid($uuid);

        if (! $asset) {
            return redirect()->to('/admin/assets')->with('error', 'Asset tidak ditemukan.');
        }

        if (! $this->canAccessLaboratory((int) $asset['laboratory_id'])) {
            return redirect()->to('/admin/assets')->with('error', 'Anda hanya dapat mengelola asset pada laboratorium yang ditugaskan.');
        }

        if (! $this->isValidSubmission((int) $asset['id'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $assetData = $this->assetData($asset['photo'] ?? null);
        $this->assetModel->update($asset['id'], $assetData);

        if (($asset['photo'] ?? null) !== ($assetData['photo'] ?? null)) {
            $this->deletePhoto($asset['photo']);
        }

        return redirect()->to('/admin/assets')->with('success', 'Asset berhasil diperbarui.');
    }

    public function delete(string $uuid)
    {
        $asset = $this->assetModel->findByUuid($uuid);

        if (! $asset) {
            return redirect()->to('/admin/assets')->with('error', 'Asset tidak ditemukan.');
        }

        if (! $this->canAccessLaboratory((int) $asset['laboratory_id'])) {
            return redirect()->to('/admin/assets')->with('error', 'Anda hanya dapat mengelola asset pada laboratorium yang ditugaskan.');
        }

        $this->assetModel->delete($asset['id']);

        return redirect()->to('/admin/assets')->with('success', 'Asset berhasil dihapus.');
    }

    private function renderForm(string $title, string $pageTitle, ?array $asset = null)
    {
        return $this->renderView($asset ? 'assets/edit' : 'assets/create', [
            'title' => $title,
            'page_title' => $pageTitle,
            'asset' => $asset,
            'laboratories' => $this->laboratoryOptions(),
            'statuses' => AssetModel::statuses(),
        ]);
    }

    private function assetFilters(): array
    {
        $status = (string) $this->request->getGet('status');
        $borrowable = (string) $this->request->getGet('can_be_borrowed');

        return [
            'search' => trim((string) $this->request->getGet('q')),
            'laboratoryId' => (int) $this->request->getGet('laboratory_id'),
            'status' => array_key_exists($status, AssetModel::statuses()) ? $status : '',
            'borrowable' => in_array($borrowable, ['0', '1'], true) ? $borrowable : '',
        ];
    }

    private function assetQuery(array $filters)
    {
        $query = $this->assetModel
            ->select('assets.*, laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name')
            ->join('laboratories', 'laboratories.id = assets.laboratory_id')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->orderBy('assets.asset_code', 'ASC');

        if ($filters['search'] !== '') {
            $query->groupStart()
                ->like('assets.asset_code', $filters['search'])
                ->orLike('assets.name', $filters['search'])
                ->orLike('assets.category', $filters['search'])
                ->orLike('assets.brand', $filters['search'])
                ->orLike('assets.model', $filters['search'])
                ->orLike('assets.serial_number', $filters['search'])
                ->orLike('laboratories.name', $filters['search'])
                ->orLike('rooms.code', $filters['search'])
                ->orLike('rooms.name', $filters['search'])
                ->groupEnd();
        }

        if ($filters['laboratoryId'] > 0) {
            $query->where('assets.laboratory_id', $filters['laboratoryId']);
        }

        if ($filters['status'] !== '') {
            $query->where('assets.status', $filters['status']);
        }

        if ($filters['borrowable'] !== '') {
            $query->where('assets.can_be_borrowed', (int) $filters['borrowable']);
        }

        if (activeGroupIs('laboran')) {
            $laboratoryIds = $this->assignedLaboratoryIds();

            if (empty($laboratoryIds)) {
                $query->where('assets.laboratory_id', 0);
            } else {
                $query->whereIn('assets.laboratory_id', $laboratoryIds);
            }
        }

        return $query;
    }

    private function isValidSubmission(?int $id = null): bool
    {
        $uniqueCode = $id === null ? 'is_unique[assets.asset_code]' : "is_unique[assets.asset_code,id,{$id}]";

        $rules = [
            'asset_code' => "required|alpha_numeric_punct|min_length[2]|max_length[50]|{$uniqueCode}",
            'name' => 'required|min_length[3]|max_length[150]',
            'laboratory_id' => 'required|integer|is_not_unique[laboratories.id]',
            'category' => 'permit_empty|max_length[100]',
            'brand' => 'permit_empty|max_length[100]',
            'model' => 'permit_empty|max_length[100]',
            'serial_number' => 'permit_empty|max_length[100]',
            'acquisition_date' => 'permit_empty|valid_date[Y-m-d]',
            'purchase_price' => 'permit_empty|decimal|greater_than_equal_to[0]',
            'can_be_borrowed' => 'required|in_list[0,1]',
            'status' => 'required|in_list[' . implode(',', array_keys(AssetModel::statuses())) . ']',
            'description' => 'permit_empty|max_length[500]',
        ];

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->getError() !== UPLOAD_ERR_NO_FILE) {
            $rules['photo'] = 'uploaded[photo]|max_size[photo,2048]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]';
        }

        if (! $this->validate($rules)) {
            return false;
        }

        $laboratory = $this->laboratoryModel
            ->where('status', 'active')
            ->find((int) $this->request->getPost('laboratory_id'));

        if (! $laboratory) {
            $this->validator->setError('laboratory_id', 'Laboratorium yang dipilih tidak tersedia atau tidak aktif.');

            return false;
        }

        if (! $this->canAccessLaboratory((int) $this->request->getPost('laboratory_id'))) {
            $this->validator->setError('laboratory_id', 'Anda hanya dapat memilih laboratorium yang ditugaskan kepada Anda.');

            return false;
        }

        if ($this->request->getPost('can_be_borrowed') === '0' && $this->request->getPost('status') === AssetModel::STATUS_BORROWED) {
            $this->validator->setError('status', 'Asset yang tidak boleh dipinjam tidak dapat berstatus sedang dipinjam.');

            return false;
        }

        return true;
    }

    private function assetData(?string $currentPhoto = null): array
    {
        $purchasePrice = $this->request->getPost('purchase_price');
        $photo = $this->storePhoto($currentPhoto);

        return [
            'asset_code' => strtoupper(trim((string) $this->request->getPost('asset_code'))),
            'name' => trim((string) $this->request->getPost('name')),
            'photo' => $photo,
            'laboratory_id' => (int) $this->request->getPost('laboratory_id'),
            'category' => trim((string) $this->request->getPost('category')),
            'brand' => trim((string) $this->request->getPost('brand')),
            'model' => trim((string) $this->request->getPost('model')),
            'serial_number' => trim((string) $this->request->getPost('serial_number')),
            'acquisition_date' => $this->request->getPost('acquisition_date') ?: null,
            'purchase_price' => $purchasePrice === '' ? null : $purchasePrice,
            'can_be_borrowed' => (int) $this->request->getPost('can_be_borrowed'),
            'status' => $this->request->getPost('status'),
            'description' => trim((string) $this->request->getPost('description')),
        ];
    }

    private function storePhoto(?string $currentPhoto = null): ?string
    {
        $photo = $this->request->getFile('photo');

        if (! $photo || $photo->getError() === UPLOAD_ERR_NO_FILE) {
            return $currentPhoto;
        }

        $uploadPath = FCPATH . 'uploads/assets';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $photo->getExtension();
        $photo->move($uploadPath, $fileName);

        return 'uploads/assets/' . $fileName;
    }

    private function deletePhoto(?string $photo): void
    {
        if ($photo && str_starts_with($photo, 'uploads/assets/')) {
            $filePath = FCPATH . $photo;
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }

    private function laboratoryOptions(): array
    {
        $laboratories = $this->laboratoryModel
            ->select('laboratories.*, rooms.code AS room_code, rooms.name AS room_name')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->where('laboratories.status', 'active')
            ->orderBy('laboratories.name', 'ASC');

        if (activeGroupIs('laboran')) {
            $laboratoryIds = $this->assignedLaboratoryIds();

            if (empty($laboratoryIds)) {
                return [];
            }

            $laboratories->whereIn('laboratories.id', $laboratoryIds);
        }

        return $laboratories->findAll();
    }

    private function canAccessLaboratory(int $laboratoryId): bool
    {
        if (! activeGroupIs('laboran')) {
            return true;
        }

        return in_array($laboratoryId, $this->assignedLaboratoryIds(), true);
    }

    private function assignedLaboratoryIds(): array
    {
        if (! auth()->loggedIn()) {
            return [];
        }

        return array_map('intval', array_column(
            $this->laboratoryLaboranModel
                ->select('laboratory_laborans.laboratory_id')
                ->join('laboratories', 'laboratories.id = laboratory_laborans.laboratory_id')
                ->where('laboratory_laborans.user_id', auth()->user()->id)
                ->where('laboratories.status', 'active')
                ->findAll(),
            'laboratory_id'
        ));
    }
}