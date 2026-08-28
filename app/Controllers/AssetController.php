<?php

namespace App\Controllers;

use App\Models\AssetModel;
use App\Models\LaboratoryLaboranModel;
use App\Models\LaboratoryModel;

class AssetController extends BaseController
{
    protected AssetModel $assetModel;
    protected LaboratoryLaboranModel $laboratoryLaboranModel;
    protected LaboratoryModel $laboratoryModel;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function __construct()
    {
        $this->assetModel = new AssetModel();
        $this->laboratoryLaboranModel = new LaboratoryLaboranModel();
        $this->laboratoryModel = new LaboratoryModel();
    }

    public function index()
    {
        $search        = trim((string) $this->request->getGet('q'));
        $laboratoryId  = (int) $this->request->getGet('laboratory_id');
        $status        = (string) $this->request->getGet('status');
        $borrowable    = (string) $this->request->getGet('can_be_borrowed');
        $perPage       = (int) $this->request->getGet('perPage');

        if (! array_key_exists($status, AssetModel::statuses())) {
            $status = '';
        }

        if (! in_array($borrowable, ['0', '1'], true)) {
            $borrowable = '';
        }

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $query = $this->assetModel
            ->select('assets.*, laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name')
            ->join('laboratories', 'laboratories.id = assets.laboratory_id')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->orderBy('assets.asset_code', 'ASC');

        if ($search !== '') {
            $query->groupStart()
                ->like('assets.asset_code', $search)
                ->orLike('assets.name', $search)
                ->orLike('assets.category', $search)
                ->orLike('assets.brand', $search)
                ->orLike('assets.model', $search)
                ->orLike('assets.serial_number', $search)
                ->orLike('laboratories.name', $search)
                ->orLike('rooms.code', $search)
                ->orLike('rooms.name', $search)
                ->groupEnd();
        }

        if ($laboratoryId > 0) {
            $query->where('assets.laboratory_id', $laboratoryId);
        }

        if ($status !== '') {
            $query->where('assets.status', $status);
        }

        if ($borrowable !== '') {
            $query->where('assets.can_be_borrowed', (int) $borrowable);
        }

        if (activeGroupIs('laboran')) {
            $laboratoryIds = $this->assignedLaboratoryIds();

            if (empty($laboratoryIds)) {
                $query->where('assets.laboratory_id', 0);
            } else {
                $query->whereIn('assets.laboratory_id', $laboratoryIds);
            }
        }

        $assets = $query->paginate($perPage);
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