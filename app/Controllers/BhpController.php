<?php

namespace App\Controllers;

use App\Models\BhpAuditLogModel;
use App\Models\BhpEvidenceModel;
use App\Models\BhpItemModel;
use App\Models\BhpItemOverrideModel;
use App\Models\BhpLaboranSubmissionModel;
use App\Models\BhpPeriodModel;
use App\Models\BhpRequestModel;
use App\Models\LaboratoryModel;
use App\Models\StudyProgramModel;

class BhpController extends BaseController
{
    private const STATUSES = ['DRAFT', 'PENDING_REVIEW', 'NEED_REVISION', 'APPROVED_BY_KALAB', 'FUND_DISBURSED', 'EVIDEN_SUBMITTED', 'COMPLETED', 'REJECTED'];
    private const UNITS = ['Pcs', 'Box', 'Roll', 'Liter', 'Rim', 'Bottle', 'Pack', 'Unit', 'Custom'];
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    protected BhpRequestModel $requestModel;
    protected BhpItemModel $itemModel;
    protected BhpItemOverrideModel $itemOverrideModel;
    protected BhpLaboranSubmissionModel $laboranSubmissionModel;
    protected BhpPeriodModel $periodModel;
    protected BhpEvidenceModel $evidenceModel;
    protected BhpAuditLogModel $auditModel;
    protected LaboratoryModel $laboratoryModel;
    protected StudyProgramModel $studyProgramModel;

    public function __construct()
    {
        $this->requestModel = new BhpRequestModel();
        $this->itemModel = new BhpItemModel();
        $this->itemOverrideModel = new BhpItemOverrideModel();
        $this->laboranSubmissionModel = new BhpLaboranSubmissionModel();
        $this->periodModel = new BhpPeriodModel();
        $this->evidenceModel = new BhpEvidenceModel();
        $this->auditModel = new BhpAuditLogModel();
        $this->laboratoryModel = new LaboratoryModel();
        $this->studyProgramModel = new StudyProgramModel();
    }

    public function index()
    {
        $editableItemCountSql = 'SELECT COUNT(*) FROM pengajuan_bhp_item WHERE pengajuan_bhp_item.pengajuan_id = pengajuan_bhp.id AND pengajuan_bhp_item.deleted_at IS NULL';
        if (activeGroupIs('laboran', 'user')) {
            $editableItemCountSql .= ' AND pengajuan_bhp_item.laboran_id = ' . (int) auth()->id();
        }

        $query = $this->requestModel
            ->select('pengajuan_bhp.*, users.username, laboratories.name AS laboratory_name, study_programs.name AS study_program_name, periode_pengajuan.nama_periode, periode_pengajuan.tanggal_mulai AS periode_tanggal_mulai, periode_pengajuan.tanggal_selesai AS periode_tanggal_selesai, (' . $editableItemCountSql . ') AS editable_item_count', false)
            ->join('users', 'users.id = pengajuan_bhp.laboran_id', 'left')
            ->join('laboratories', 'laboratories.id = pengajuan_bhp.laboratory_id', 'left')
            ->join('study_programs', 'study_programs.id = pengajuan_bhp.study_program_id', 'left')
            ->join('periode_pengajuan', 'periode_pengajuan.id = pengajuan_bhp.periode_id');

        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $periodId = (int) $this->request->getGet('periode_id');
        $perPage = (int) $this->request->getGet('perPage');
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }
        if (in_array(activeGroup(), ['laboran', 'user'], true)) {
            $query->join('laboratory_study_programs pocket_programs', 'pocket_programs.study_program_id = pengajuan_bhp.study_program_id', 'inner')
                ->join('laboratory_laborans pocket_assignments', 'pocket_assignments.laboratory_id = pocket_programs.laboratory_id AND pocket_assignments.user_id = ' . (int) auth()->id(), 'inner')->distinct();
        }
        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $query->where('pengajuan_bhp.status', $status);
        }
        if ($periodId > 0) {
            $query->where('pengajuan_bhp.periode_id', $periodId);
        }
        if ($search !== '') {
            $query->groupStart()->like('pengajuan_bhp.kode_pengajuan', $search)->orLike('laboratories.name', $search)->orLike('users.username', $search)->orLike('pengajuan_bhp.status', $search)->groupEnd();
        }

        $requests = $query->orderBy('pengajuan_bhp.created_at', 'DESC')->paginate($perPage);
        $pager = $this->requestModel->pager;
        return $this->renderView('bhp/index', [
            'title' => 'Pengajuan BHP', 'page_title' => 'Pengajuan Bahan Habis Pakai',
            'requests' => $requests, 'pager' => $pager,
            'search' => $search, 'status' => $status, 'periodId' => $periodId, 'statuses' => self::STATUSES,
            'periods' => $this->periodModel->orderBy('tanggal_mulai', 'DESC')->findAll(),
            'availablePrograms' => $this->availableBhpPrograms(),
            'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $pager->getCurrentPage(), 'totalRows' => $pager->getTotal(),
        ]);
    }

    public function create()
    {
        $period = $this->periodModel->active();
        if (! $period && ! activeGroupIs('superadmin')) {
            return redirect()->to('/bhp')->with('error', 'Jendela pengajuan sedang ditutup.');
        }
        $pocket = $this->request->getGet('pocket_uuid')
            ? $this->requestModel->findByUuid((string) $this->request->getGet('pocket_uuid'))
            : $this->requestModel->where(['periode_id' => (int) $this->request->getGet('periode_id'), 'study_program_id' => (int) $this->request->getGet('study_program_id')])->first();
        if (! $pocket) return redirect()->to('/bhp')->with('error', 'Pengajuan program studi tidak ditemukan.');
        if (! $this->canAccessPocket($pocket)) return redirect()->to('/bhp')->with('error', 'Pengajuan program studi tidak tersedia untuk Anda.');
        return $this->renderView('bhp/form', [
            'title' => 'Tambah Item BHP', 'page_title' => 'Tambah Item BHP', 'requestData' => null,
            'pocket' => $pocket, 'period' => $this->periodModel->find($pocket['periode_id']), 'laboratories' => $this->availableLaboratories(),
            'periods' => activeGroupIs('superadmin') ? $this->periodModel->orderBy('tanggal_mulai', 'DESC')->findAll() : ($period ? [$period] : []),
            'studyPrograms' => $this->studyProgramModel->orderBy('name')->findAll(),
            'laboratoryStudyPrograms' => $this->laboratoryStudyPrograms(), 'studyProgramLaboratories' => $this->studyProgramLaboratories(), 'units' => self::UNITS,
            'selectedStudyProgramId' => (int) $pocket['study_program_id'], 'selectedPeriodId' => (int) $pocket['periode_id'],
        ]);
    }

    public function store()
    {
        if (! activeGroupIs('superadmin') && ! $this->periodModel->active()) {
            return redirect()->back()->withInput()->with('error', 'Jendela pengajuan sedang ditutup.');
        }
        $data = $this->requestData();
        if (! $this->validateData($data, $this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $period = $this->periodModel->find((int) $data['periode_id']);
        if (! $period || (! activeGroupIs('superadmin') && ! $this->periodModel->active())) {
            return redirect()->back()->withInput()->with('error', 'Periode pengajuan tidak aktif.');
        }
        $items = $this->postedItems();
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Tambahkan minimal satu item BHP.');
        }
        foreach ($items as $item) {
            if (! $this->validItemLaboratory((int) $item['laboratory_id'], (int) $data['study_program_id'])) {
                return redirect()->back()->withInput()->with('error', 'Setiap item harus memakai laboratorium yang ditugaskan kepada Anda dan berada di bawah program studi yang dipilih.');
            }
        }
        $firstLaboratory = $this->laboratoryModel->find((int) $items[0]['laboratory_id']);
        $db = db_connect();
        $request = $this->requestModel->where(['periode_id' => $period['id'], 'study_program_id' => $data['study_program_id']])->first();
        if ($request && ! in_array($request['status'], ['DRAFT', 'NEED_REVISION'], true)) {
            return redirect()->back()->withInput()->with('error', 'Pengajuan sudah dikunci dan tidak menerima item baru.');
        }
        $db->transStart();
        if (! $request) {
            $this->requestModel->insert([
                'kode_pengajuan' => $this->pocketCode($period['id'], (int) $data['study_program_id']),
                'periode_id' => $period['id'], 'laboran_id' => null, 'laboratory_id' => null,
                'study_program_id' => $data['study_program_id'], 'nama_lab_snapshot' => 'Multi laboratorium',
                'prodi_snapshot' => $this->studyProgramName((int) $data['study_program_id']), 'grand_total_estimasi' => 0, 'status' => 'DRAFT',
            ]);
            $requestId = (int) $this->requestModel->getInsertID();
        } else {
            $requestId = (int) $request['id'];
        }
        $this->laboranSubmissionModel->where(['pengajuan_id' => $requestId, 'laboran_id' => auth()->id()])->delete();
        foreach ($items as $item) {
            $item['pengajuan_id'] = $requestId;
            $item['laboran_id'] = auth()->id();
            $this->itemModel->insert($item);
        }
        $this->recalculateTotal($requestId);
        $db->transComplete();
        if (! $db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Pengajuan BHP gagal disimpan.');
        }
        return redirect()->to('/bhp')->with('success', 'Draft pengajuan BHP berhasil dibuat.');
    }

    public function edit(string $uuid)
    {
        $requestData = $this->accessible($uuid);
        if (! $requestData || ! in_array($requestData['status'], ['DRAFT', 'NEED_REVISION'], true)) {
            return redirect()->to('/bhp')->with('error', 'Pengajuan tidak dapat diedit.');
        }
        $period = $this->periodModel->find($requestData['periode_id']);
        if (! activeGroupIs('superadmin') && ! $this->periodIsActive($period)) {
            return redirect()->to('/bhp')->with('error', 'Jendela pengajuan sedang ditutup.');
        }
        return $this->renderView('bhp/form', [
            'title' => 'Edit Pengajuan BHP', 'page_title' => 'Edit Pengajuan BHP', 'requestData' => $requestData,
            'period' => $period, 'laboratories' => $this->availableLaboratories(),
            'periods' => [$period],
            'studyPrograms' => $this->studyProgramModel->orderBy('name')->findAll(),
            'laboratoryStudyPrograms' => $this->laboratoryStudyPrograms(), 'studyProgramLaboratories' => $this->studyProgramLaboratories(), 'units' => self::UNITS,
            'items' => $this->editableItems($requestData),
        ]);
    }

    public function update(string $uuid)
    {
        $requestData = $this->accessible($uuid);
        if (! $requestData || ! in_array($requestData['status'], ['DRAFT', 'NEED_REVISION'], true)) {
            return redirect()->to('/bhp')->with('error', 'Pengajuan tidak dapat diedit.');
        }
        $period = $this->periodModel->find($requestData['periode_id']);
        if (! activeGroupIs('superadmin') && ! $this->periodIsActive($period)) {
            return redirect()->to('/bhp')->with('error', 'Jendela pengajuan sedang ditutup.');
        }
        $data = $this->requestData();
        if (! $this->validateData($data, $this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $items = $this->postedItems();
        if (empty($items)) return redirect()->back()->withInput()->with('error', 'Tambahkan minimal satu item BHP.');
        foreach ($items as $item) {
            if (! $this->validItemLaboratory((int) $item['laboratory_id'], (int) $requestData['study_program_id'])) {
                return redirect()->back()->withInput()->with('error', 'Setiap item harus memakai laboratorium yang ditugaskan kepada Anda dan berada di bawah program studi kantong.');
            }
        }
        $db = db_connect();
        $db->transStart();
        $this->laboranSubmissionModel->where(['pengajuan_id' => $requestData['id'], 'laboran_id' => auth()->id()])->delete();
        $this->itemModel->where(['pengajuan_id' => $requestData['id'], 'laboran_id' => auth()->id()])->delete(null, true);
        foreach ($items as $item) {
            $item['pengajuan_id'] = $requestData['id'];
            $item['laboran_id'] = auth()->id();
            $this->itemModel->insert($item);
        }
        $this->requestModel->update($requestData['id'], ['catatan_revisi' => null]);
        $this->recalculateTotal((int) $requestData['id']);
        $db->transComplete();
        return $db->transStatus() ? redirect()->to('/bhp/edit/' . $uuid)->with('success', 'Pengajuan BHP berhasil diperbarui.') : redirect()->back()->withInput()->with('error', 'Pengajuan BHP gagal diperbarui.');
    }

    public function submit(string $uuid)
    {
        $requestData = $this->accessible($uuid);
        if (! $requestData || ! in_array($requestData['status'], ['DRAFT', 'NEED_REVISION'], true)) {
            return redirect()->to('/bhp')->with('error', 'Hanya pengajuan draft atau revisi yang dapat diajukan untuk review.');
        }
        $period = $this->periodModel->find($requestData['periode_id']);
        if (! activeGroupIs('superadmin') && (! $period || ! $this->periodIsActive($period))) {
            return redirect()->to('/bhp')->with('error', 'Jendela pengajuan sedang ditutup.');
        }
        $laboranIds = $this->requestLaboranIds((int) $requestData['id']);
        if (empty($laboranIds)) {
            return redirect()->to('/bhp')->with('error', 'Pengajuan harus memiliki minimal satu item.');
        }
        $readyLaboranIds = $this->readyLaboranIds((int) $requestData['id']);
        if (array_diff($laboranIds, $readyLaboranIds)) {
            return redirect()->to('/bhp/detail/' . $uuid)->with('error', 'Pengajuan menunggu semua laboran menandai itemnya siap review.');
        }
        $this->transition($requestData, 'PENDING_REVIEW', 'Pengajuan siap ditinjau oleh kepala lab.');
        if (activeGroupIs('superadmin') && ! $this->periodIsActive($period)) {
            $this->auditModel->insert(['admin_id' => auth()->id(), 'action_type' => 'OVERRIDE_WINDOW', 'target_entity_id' => $requestData['id'], 'notes' => 'Submit di luar periode aktif.']);
        }
        return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Pengajuan BHP berhasil diajukan untuk review.');
    }

    public function readyForReview(string $uuid)
    {
        $requestData = $this->accessible($uuid);
        if (! $requestData || ! in_array($requestData['status'], ['DRAFT', 'NEED_REVISION'], true)) {
            return redirect()->to('/bhp')->with('error', 'Pengajuan tidak dapat disiapkan untuk review.');
        }
        if (! activeGroupIs('laboran')) {
            return redirect()->to('/bhp/detail/' . $uuid)->with('error', 'Hanya laboran pengaju yang dapat menandai item siap review.');
        }
        $period = $this->periodModel->find($requestData['periode_id']);
        if (! $this->periodIsActive($period)) {
            return redirect()->to('/bhp/detail/' . $uuid)->with('error', 'Jendela pengajuan sedang ditutup.');
        }

        $laboranId = (int) auth()->id();
        if (! in_array($laboranId, $this->requestLaboranIds((int) $requestData['id']), true)) {
            return redirect()->to('/bhp/detail/' . $uuid)->with('error', 'Anda belum memiliki item pada pengajuan ini.');
        }

        if (! $this->laboranSubmissionModel->where(['pengajuan_id' => $requestData['id'], 'laboran_id' => $laboranId])->first()) {
            $this->laboranSubmissionModel->insert(['pengajuan_id' => $requestData['id'], 'laboran_id' => $laboranId, 'submitted_at' => date('Y-m-d H:i:s')]);
        }

        $laboranIds = $this->requestLaboranIds((int) $requestData['id']);
        $readyLaboranIds = $this->readyLaboranIds((int) $requestData['id']);
        if (! array_diff($laboranIds, $readyLaboranIds)) {
            $this->transition($requestData, 'PENDING_REVIEW', 'Semua laboran telah menandai itemnya siap review.');
            return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Semua laboran siap. Pengajuan dikirim untuk review kepala lab.');
        }

        return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Item Anda sudah ditandai siap review.');
    }

    public function overrideItem(string $itemUuid)
    {
        if (! activeGroupIs('superadmin', 'kepala_lab')) {
            return redirect()->to('/bhp')->with('error', 'Hanya kepala lab yang dapat melakukan override item.');
        }

        $item = $this->itemModel->where('uuid', $itemUuid)->first();
        $reason = trim((string) $this->request->getPost('reason'));
        if (! $item || $reason === '') {
            return redirect()->back()->with('error', 'Item tidak ditemukan atau alasan override wajib diisi.');
        }

        $requestData = $this->requestModel->find((int) $item['pengajuan_id']);
        if (! $requestData || in_array($requestData['status'], ['FUND_DISBURSED', 'EVIDEN_SUBMITTED', 'COMPLETED'], true)) {
            return redirect()->back()->with('error', 'Item tidak dapat diubah pada status pengajuan saat ini.');
        }

        $rules = [
            'nama_barang' => 'required|max_length[255]',
            'spesifikasi' => 'permit_empty',
            'qty' => 'required|is_natural_no_zero',
            'satuan' => 'required|max_length[50]',
            'harga_satuan' => 'required|numeric|greater_than_equal_to[0]',
            'vendor' => 'required|max_length[150]',
            'link_toko_online' => 'required|valid_url',
        ];
        $posted = $this->request->getPost();
        if (! $this->validateData($posted, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $before = $this->itemSnapshot($item);
        $after = [
            'nama_barang' => trim((string) $posted['nama_barang']),
            'spesifikasi' => trim((string) ($posted['spesifikasi'] ?? '')),
            'qty' => (int) $posted['qty'],
            'satuan' => (string) $posted['satuan'],
            'harga_satuan' => (float) $posted['harga_satuan'],
            'total_harga' => (int) $posted['qty'] * (float) $posted['harga_satuan'],
            'vendor' => trim((string) $posted['vendor']),
            'link_toko_online' => trim((string) $posted['link_toko_online']),
        ];

        $db = db_connect();
        $db->transStart();
        $this->itemModel->update($item['id'], $after);
        $this->itemOverrideModel->insert([
            'item_id' => $item['id'], 'pengajuan_id' => $item['pengajuan_id'], 'changed_by' => auth()->id(),
            'before_data' => json_encode($before, JSON_UNESCAPED_UNICODE), 'after_data' => json_encode($after, JSON_UNESCAPED_UNICODE),
            'reason' => $reason, 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->recalculateTotal((int) $item['pengajuan_id']);
        $db->transComplete();

        return $db->transStatus()
            ? redirect()->to('/bhp/detail/' . $requestData['uuid'])->with('success', 'Item berhasil di-override dan perubahannya tercatat.')
            : redirect()->back()->with('error', 'Override item gagal disimpan.');
    }

    public function approvalIndex()
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $query = $this->requestModel->select('pengajuan_bhp.*, laboratories.name AS laboratory_name, study_programs.name AS study_program_name')
            ->join('laboratories', 'laboratories.id = pengajuan_bhp.laboratory_id', 'left')
            ->join('study_programs', 'study_programs.id = pengajuan_bhp.study_program_id', 'left')
            ->whereIn('pengajuan_bhp.status', ['PENDING_REVIEW', 'EVIDEN_SUBMITTED']);
        if (in_array($status, ['PENDING_REVIEW', 'EVIDEN_SUBMITTED'], true)) $query->where('pengajuan_bhp.status', $status);
        else $status = '';
        if ($search !== '') $query->groupStart()->like('pengajuan_bhp.kode_pengajuan', $search)->orLike('laboratories.name', $search)->orLike('study_programs.name', $search)->orLike('pengajuan_bhp.nama_lab_snapshot', $search)->groupEnd();
        $requests = $query->orderBy('pengajuan_bhp.created_at', 'ASC')->paginate(15);
        return $this->renderView('bhp/approval', ['title' => 'Review Pengajuan BHP', 'page_title' => 'Review Pengajuan BHP', 'requests' => $requests, 'pager' => $this->requestModel->pager, 'search' => $search, 'status' => $status]);
    }

    public function approve(string $uuid)
    {
        return $this->review($uuid, 'APPROVED_BY_KALAB', 'Pengajuan disetujui.');
    }

    public function revise(string $uuid)
    {
        return $this->review($uuid, 'NEED_REVISION', trim((string) $this->request->getPost('note')), true);
    }

    public function reject(string $uuid)
    {
        return $this->review($uuid, 'REJECTED', trim((string) $this->request->getPost('note')), true);
    }

    public function disburse(string $uuid)
    {
        if (! activeGroupIs('superadmin', 'kepala_lab')) return redirect()->to('/bhp')->with('error', 'Grup aktif tidak dapat menandai pencairan.');
        $data = $this->accessible($uuid);
        $nominal = (float) $this->request->getPost('nominal_cair');
        $date = trim((string) $this->request->getPost('tanggal_cair'));
        if (! $data || $data['status'] !== 'APPROVED_BY_KALAB' || $nominal < 0 || $date === '') return redirect()->to('/bhp')->with('error', 'Data pencairan tidak valid.');
        $this->requestModel->update($data['id'], ['status' => 'FUND_DISBURSED', 'nominal_cair' => $nominal, 'tanggal_cair' => $date]);
        $this->history($data['id'], $data['status'], 'FUND_DISBURSED', 'Anggaran ditandai cair.');
        return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Anggaran berhasil ditandai cair.');
    }

    public function evidence(string $uuid)
    {
        $data = $this->accessible($uuid);
        if (! $data || ! in_array($data['status'], ['FUND_DISBURSED'], true)) return redirect()->to('/bhp')->with('error', 'Eviden hanya dapat diunggah setelah dana cair.');
        $rules = ['tanggal_belanja' => 'required|valid_date[Y-m-d]', 'realisasi_biaya' => 'required|numeric|greater_than_equal_to[0]', 'dokumen_nota_kwitansi' => 'uploaded[dokumen_nota_kwitansi]|max_size[dokumen_nota_kwitansi,5120]|mime_in[dokumen_nota_kwitansi,application/pdf,image/jpg,image/jpeg,image/png]'];
        if (! $this->validateData($this->request->getPost(), $rules)) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        $photos = $this->request->getFileMultiple('foto_barang');
        if (empty($photos) || ! array_filter($photos, static fn ($file) => $file && $file->isValid() && ! $file->hasMoved())) return redirect()->back()->withInput()->with('error', 'Minimal satu foto barang wajib diunggah.');
        $path = WRITEPATH . 'uploads/bhp/' . $data['uuid'];
        if (! is_dir($path)) mkdir($path, 0750, true);
        foreach ($photos as $photo) $this->storeEvidence($photo, $data['id'], 'FOTO_BARANG', $path);
        $receipt = $this->request->getFile('dokumen_nota_kwitansi');
        $this->storeEvidence($receipt, $data['id'], 'KWITANSI_NOTA', $path);
        $this->requestModel->update($data['id'], ['status' => 'EVIDEN_SUBMITTED', 'tanggal_belanja' => $this->request->getPost('tanggal_belanja'), 'realisasi_biaya' => $this->request->getPost('realisasi_biaya'), 'catatan_pembelian' => trim((string) $this->request->getPost('catatan_pembelian'))]);
        $this->history($data['id'], $data['status'], 'EVIDEN_SUBMITTED', 'Eviden belanja diunggah.');
        return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Eviden berhasil dikirim untuk diverifikasi.');
    }

    public function verify(string $uuid)
    {
        if (! activeGroupIs('superadmin', 'kepala_lab')) return redirect()->to('/bhp')->with('error', 'Grup aktif tidak dapat memverifikasi eviden.');
        $data = $this->accessible($uuid);
        if (! $data || $data['status'] !== 'EVIDEN_SUBMITTED') return redirect()->to('/bhp')->with('error', 'Status eviden tidak valid.');
        $note = trim((string) $this->request->getPost('note'));
        $this->requestModel->update($data['id'], ['status' => 'COMPLETED', 'catatan_verifikasi' => $note]);
        $this->history($data['id'], $data['status'], 'COMPLETED', $note ?: 'Eviden divalidasi dan pengajuan ditutup.');
        return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Pengajuan BHP berhasil diselesaikan.');
    }

    public function rejectEvidence(string $uuid)
    {
        if (! activeGroupIs('superadmin', 'kepala_lab')) return redirect()->to('/bhp')->with('error', 'Grup aktif tidak dapat menolak eviden.');
        $data = $this->accessible($uuid);
        $note = trim((string) $this->request->getPost('note'));
        if (! $data || $data['status'] !== 'EVIDEN_SUBMITTED' || $note === '') return redirect()->to('/bhp')->with('error', 'Catatan penolakan eviden wajib diisi.');
        $this->requestModel->update($data['id'], ['status' => 'FUND_DISBURSED', 'catatan_verifikasi' => $note]);
        $this->history($data['id'], $data['status'], 'FUND_DISBURSED', $note);
        return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Eviden dikembalikan untuk diperbaiki.');
    }

    public function detail(string $uuid)
    {
        $data = $this->accessible($uuid);
        if (! $data) return redirect()->to('/bhp')->with('error', 'Pengajuan tidak ditemukan.');
        $items = $this->itemModel->select('pengajuan_bhp_item.*, users.username AS laboran_name, laboratories.name AS laboratory_name')
            ->join('users', 'users.id = pengajuan_bhp_item.laboran_id', 'left')
            ->join('laboratories', 'laboratories.id = pengajuan_bhp_item.laboratory_id', 'left')
            ->where('pengajuan_id', $data['id'])->findAll();
        $overrides = $this->itemOverrideModel->select('pengajuan_bhp_item_override.*, users.username AS changed_by_name')
            ->join('users', 'users.id = pengajuan_bhp_item_override.changed_by')
            ->where('pengajuan_id', $data['id'])->orderBy('created_at', 'DESC')->findAll();
        $history = (new \App\Models\BhpStatusHistoryModel())
            ->select('pengajuan_bhp_status_history.*, users.username AS changed_by_name')
            ->join('users', 'users.id = pengajuan_bhp_status_history.changed_by', 'left')
            ->where('pengajuan_id', $data['id'])->orderBy('pengajuan_bhp_status_history.id', 'DESC')->findAll();
        return $this->renderView('bhp/detail', ['title' => 'Detail Pengajuan BHP', 'page_title' => 'Detail pengajuan', 'requestData' => $data, 'items' => $items, 'overrides' => $overrides, 'evidences' => $this->evidenceModel->where('pengajuan_id', $data['id'])->findAll(), 'history' => $history, 'laboranSubmissions' => $this->laboranSubmissionModel->where('pengajuan_id', $data['id'])->findAll()]);
    }

    public function downloadEvidence(string $uuid, string $evidenceUuid)
    {
        $data = $this->accessible($uuid);
        $evidence = $this->evidenceModel->where(['uuid' => $evidenceUuid, 'pengajuan_id' => $data['id'] ?? 0])->first();
        if (! $data || ! $evidence) return $this->response->setStatusCode(404)->setBody('Eviden tidak ditemukan.');
        $fullPath = WRITEPATH . 'uploads/' . $evidence['file_path'];
        if (! is_file($fullPath)) return $this->response->setStatusCode(404)->setBody('File eviden tidak ditemukan.');
        return $this->response->download($fullPath, null)->setFileName($evidence['original_name']);
    }

    public function periods()
    {
        $tab = (string) $this->request->getGet('tab') === 'archive' ? 'archive' : 'active';
        $perPage = (int) $this->request->getGet('perPage');
        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }
        $now = date('Y-m-d H:i:s');
        $query = $this->periodModel->orderBy('tanggal_mulai', 'DESC');
        if ($tab === 'archive') {
            $query->where('tanggal_selesai <', $now);
        } else {
            $query->where('tanggal_selesai >=', $now);
        }

        $periods = $query->paginate($perPage);
        $pager = $this->periodModel->pager;

        return $this->renderView('bhp/periods', [
            'title' => 'Periode Pengajuan BHP',
            'page_title' => 'Periode Pengajuan BHP',
            'periods' => $periods,
            'pager' => $pager,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $pager->getCurrentPage(),
            'totalRows' => $pager->getTotal(),
            'tab' => $tab,
        ]);
    }

    public function createPeriod()
    {
        return $this->renderView('bhp/period_form', ['title' => 'Buat Periode Pengajuan BHP', 'page_title' => 'Buat Periode Pengajuan BHP']);
    }

    public function editPeriod(int $id)
    {
        $period = $this->periodModel->find($id);
        if (! $period) {
            return redirect()->to('/bhp/periods')->with('error', 'Periode tidak ditemukan.');
        }

        return $this->renderView('bhp/period_form', [
            'title' => 'Edit Periode Pengajuan BHP',
            'page_title' => 'Edit Periode Pengajuan BHP',
            'period' => $period,
            'mode' => 'edit',
        ]);
    }

    public function storePeriod()
    {
        $data = $this->request->getPost();
        if (! $this->validateData($data, ['nama_periode' => 'required|max_length[100]', 'tanggal_mulai' => 'required|valid_date[Y-m-d\\TH:i]', 'tanggal_selesai' => 'required|valid_date[Y-m-d\\TH:i]'])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        if (strtotime($data['tanggal_selesai']) <= strtotime($data['tanggal_mulai'])) return redirect()->back()->withInput()->with('error', 'Tanggal selesai harus setelah tanggal mulai.');
        $overlap = $this->periodModel->where('tanggal_mulai <', date('Y-m-d H:i:s', strtotime($data['tanggal_selesai'])))->where('tanggal_selesai >', date('Y-m-d H:i:s', strtotime($data['tanggal_mulai'])))->first();
        if ($overlap) return redirect()->back()->withInput()->with('error', 'Periode tidak boleh overlap secara global.');
        $db = db_connect();
        $db->transStart();
        $this->periodModel->insert(['nama_periode' => $data['nama_periode'], 'tanggal_mulai' => date('Y-m-d H:i:s', strtotime($data['tanggal_mulai'])), 'tanggal_selesai' => date('Y-m-d H:i:s', strtotime($data['tanggal_selesai'])), 'created_by' => auth()->id()]);
        $periodId = (int) $this->periodModel->getInsertID();
        foreach ($this->studyProgramModel->where('status', 'active')->findAll() as $program) {
            $this->requestModel->insert([
                'kode_pengajuan' => $this->pocketCode($periodId, (int) $program['id']),
                'periode_id' => $periodId, 'study_program_id' => $program['id'], 'laboran_id' => null, 'laboratory_id' => null,
                'nama_lab_snapshot' => 'Multi laboratorium', 'prodi_snapshot' => $program['name'], 'grand_total_estimasi' => 0, 'status' => 'DRAFT',
            ]);
        }
        $db->transComplete();
        if (! $db->transStatus()) return redirect()->back()->withInput()->with('error', 'Periode dan pengajuan BHP gagal dibuat.');
        return redirect()->to('/bhp/periods')->with('success', 'Periode berhasil dibuat.');
    }

    public function updatePeriod(int $id)
    {
        $period = $this->periodModel->find($id);
        if (! $period) {
            return redirect()->to('/bhp/periods')->with('error', 'Periode tidak ditemukan.');
        }

        $data = $this->request->getPost();
        if (! $this->validateData($data, ['nama_periode' => 'required|max_length[100]', 'tanggal_mulai' => 'required|valid_date[Y-m-d\\TH:i]', 'tanggal_selesai' => 'required|valid_date[Y-m-d\\TH:i]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (strtotime($data['tanggal_selesai']) <= strtotime($data['tanggal_mulai'])) {
            return redirect()->back()->withInput()->with('error', 'Tanggal selesai harus setelah tanggal mulai.');
        }

        $overlap = $this->periodModel
            ->where('id !=', $id)
            ->where('tanggal_mulai <', date('Y-m-d H:i:s', strtotime($data['tanggal_selesai'])))
            ->where('tanggal_selesai >', date('Y-m-d H:i:s', strtotime($data['tanggal_mulai'])))
            ->first();
        if ($overlap) {
            return redirect()->back()->withInput()->with('error', 'Periode tidak boleh overlap dengan periode lain.');
        }

        $this->periodModel->update($id, [
            'nama_periode' => $data['nama_periode'],
            'tanggal_mulai' => date('Y-m-d H:i:s', strtotime($data['tanggal_mulai'])),
            'tanggal_selesai' => date('Y-m-d H:i:s', strtotime($data['tanggal_selesai'])),
        ]);

        return redirect()->to('/bhp/periods')->with('success', 'Periode berhasil diperbarui.');
    }

    private function review(string $uuid, string $status, string $note, bool $required = false)
    {
        if (! activeGroupIs('superadmin', 'kepala_lab')) return redirect()->to('/bhp')->with('error', 'Grup aktif tidak dapat meninjau pengajuan.');
        $data = $this->accessible($uuid);
        if (! $data || $data['status'] !== 'PENDING_REVIEW' || ($required && $note === '')) return redirect()->to('/bhp')->with('error', $required ? 'Catatan wajib diisi.' : 'Pengajuan tidak berada pada tahap review.');
        $this->transition($data, $status, $note);
        return redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Status pengajuan berhasil diperbarui.');
    }

    private function transition(array $data, string $to, string $note): void
    {
        $this->requestModel->update($data['id'], ['status' => $to, 'catatan_revisi' => $to === 'NEED_REVISION' ? $note : $data['catatan_revisi'], 'alasan_penolakan' => $to === 'REJECTED' ? $note : $data['alasan_penolakan']]);
        $this->history($data['id'], $data['status'], $to, $note);
    }

    private function history(int $id, ?string $from, string $to, string $note): void
    {
        (new \App\Models\BhpStatusHistoryModel())->insert(['pengajuan_id' => $id, 'from_status' => $from, 'to_status' => $to, 'changed_by' => auth()->id(), 'note' => $note, 'created_at' => date('Y-m-d H:i:s')]);
    }

    private function accessible(string $uuid): ?array
    {
        $data = $this->requestModel->findByUuid($uuid);
        if (! $data) return null;
        if (activeGroupIs('laboran', 'user') && ! $this->canAccessPocket($data)) return null;
        return $data;
    }

    private function canAccessPocket(array $pocket): bool
    {
        if (! activeGroupIs('laboran', 'user')) return true;
        return db_connect()->table('laboratory_laborans assignments')
            ->join('laboratory_study_programs programs', 'programs.laboratory_id = assignments.laboratory_id')
            ->where('assignments.user_id', auth()->id())
            ->where('programs.study_program_id', $pocket['study_program_id'])
            ->countAllResults() > 0;
    }

    private function editableItems(array $requestData): array
    {
        $query = $this->itemModel->where('pengajuan_id', $requestData['id']);
        if (activeGroupIs('laboran', 'user')) $query->where('laboran_id', auth()->id());
        return $query->findAll();
    }

    private function requestLaboranIds(int $requestId): array
    {
        return array_map('intval', array_column(
            $this->itemModel->select('laboran_id')->where('pengajuan_id', $requestId)->where('laboran_id IS NOT NULL', null, false)->groupBy('laboran_id')->findAll(),
            'laboran_id'
        ));
    }

    private function readyLaboranIds(int $requestId): array
    {
        return array_map('intval', array_column(
            $this->laboranSubmissionModel->select('laboran_id')->where('pengajuan_id', $requestId)->findAll(),
            'laboran_id'
        ));
    }

    private function recalculateTotal(int $requestId): void
    {
        $row = $this->itemModel->selectSum('total_harga')->where('pengajuan_id', $requestId)->first();
        $this->requestModel->update($requestId, ['grand_total_estimasi' => (float) ($row['total_harga'] ?? 0)]);
    }

    private function itemSnapshot(array $item): array
    {
        return [
            'nama_barang' => $item['nama_barang'], 'spesifikasi' => $item['spesifikasi'], 'qty' => (int) $item['qty'],
            'satuan' => $item['satuan'], 'harga_satuan' => (float) $item['harga_satuan'], 'total_harga' => (float) $item['total_harga'],
            'vendor' => $item['vendor'], 'link_toko_online' => $item['link_toko_online'],
        ];
    }

    private function availableLaboratories(): array
    {
        if (! activeGroupIs('laboran')) return $this->laboratoryModel->where('status', 'active')->orderBy('name')->findAll();
        return $this->laboratoryModel->select('laboratories.*')->join('laboratory_laborans', 'laboratory_laborans.laboratory_id = laboratories.id')->where('laboratory_laborans.user_id', auth()->id())->where('laboratories.status', 'active')->orderBy('name')->findAll();
    }

    private function availableBhpPrograms(): array
    {
        $programs = $this->studyProgramModel->where('status', 'active')->orderBy('name')->findAll();
        if (! activeGroupIs('laboran', 'user')) return $programs;
        $availableIds = array_map('intval', array_keys($this->studyProgramLaboratories()));
        return array_values(array_filter($programs, static fn (array $program): bool => in_array((int) $program['id'], $availableIds, true)));
    }

    private function isAssignedLaboratory(int $id): bool
    {
        return (new \App\Models\LaboratoryLaboranModel())->where(['user_id' => auth()->id(), 'laboratory_id' => $id])->first() !== null;
    }

    private function periodIsActive(?array $period): bool
    {
        $now = time();
        return $period && $now >= strtotime($period['tanggal_mulai']) && $now <= strtotime($period['tanggal_selesai']);
    }

    private function requestData(): array
    {
        return ['periode_id' => (int) $this->request->getPost('periode_id'), 'study_program_id' => (int) $this->request->getPost('study_program_id')];
    }

    private function rules(): array
    {
        return ['periode_id' => 'required|is_natural_no_zero', 'study_program_id' => 'required|is_natural_no_zero'];
    }

    private function laboratoryStudyPrograms(): array
    {
        $rows = db_connect()->table('laboratory_study_programs')
            ->select('laboratory_study_programs.laboratory_id, study_programs.id, study_programs.code, study_programs.name')
            ->join('study_programs', 'study_programs.id = laboratory_study_programs.study_program_id')
            ->where('study_programs.status', 'active')
            ->where('study_programs.deleted_at IS NULL', null, false)
            ->orderBy('study_programs.name', 'ASC')
            ->get()->getResultArray();
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(string) $row['laboratory_id']][] = ['id' => (int) $row['id'], 'code' => $row['code'], 'name' => $row['name']];
        }
        return $mapped;
    }

    private function studyProgramLaboratories(): array
    {
        $rows = db_connect()->table('laboratory_study_programs')
            ->select('laboratory_study_programs.study_program_id, laboratories.id, laboratories.name')
            ->join('laboratories', 'laboratories.id = laboratory_study_programs.laboratory_id')
            ->where('laboratories.status', 'active')
            ->orderBy('laboratories.name', 'ASC')->get()->getResultArray();
        $mapped = [];
        foreach ($rows as $row) {
            if (activeGroupIs('laboran') && ! $this->isAssignedLaboratory((int) $row['id'])) continue;
            $mapped[(string) $row['study_program_id']][] = ['id' => (int) $row['id'], 'name' => $row['name']];
        }
        return $mapped;
    }

    private function laboratoryHasStudyProgram(int $laboratoryId, int $studyProgramId): bool
    {
        return db_connect()->table('laboratory_study_programs')->where(['laboratory_id' => $laboratoryId, 'study_program_id' => $studyProgramId])->countAllResults() > 0;
    }

    private function validItemLaboratory(int $laboratoryId, int $studyProgramId): bool
    {
        $laboratory = $this->laboratoryModel->where('status', 'active')->find($laboratoryId);
        return $laboratory !== null
            && (! activeGroupIs('laboran', 'user') || $this->isAssignedLaboratory($laboratoryId))
            && $this->laboratoryHasStudyProgram($laboratoryId, $studyProgramId);
    }

    private function postedItems(): array
    {
        $names = $this->request->getPost('nama_barang') ?? [];
        $laboratories = $this->request->getPost('item_laboratory_id') ?? [];
        $items = [];
        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $qty = (int) ($this->request->getPost('qty')[$i] ?? 0);
            $price = (float) ($this->request->getPost('harga_satuan')[$i] ?? 0);
            if ($name === '' || $qty < 1 || $price < 0) continue;
            $items[] = ['laboratory_id' => (int) ($laboratories[$i] ?? 0), 'nama_barang' => $name, 'spesifikasi' => trim((string) (($this->request->getPost('spesifikasi')[$i] ?? ''))), 'qty' => $qty, 'satuan' => (string) (($this->request->getPost('satuan')[$i] ?? 'Unit')), 'harga_satuan' => $price, 'total_harga' => $qty * $price, 'vendor' => trim((string) (($this->request->getPost('vendor')[$i] ?? ''))), 'link_toko_online' => trim((string) (($this->request->getPost('link_toko_online')[$i] ?? '')))];
        }
        return $items;
    }

    private function nextCode(string $lab, int $periodId): string
    {
        $prefix = 'BHP/' . strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $lab)) . '/' . date('Y/m');
        $count = $this->requestModel->like('kode_pengajuan', $prefix . '/', 'after')->where('periode_id', $periodId)->countAllResults() + 1;
        return $prefix . '/' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
    }

    private function pocketCode(int $periodId, int $studyProgramId): string
    {
        return 'BHP/PENGAJUAN/' . $periodId . '/' . $studyProgramId;
    }

    private function studyProgramName(int $id): ?string
    {
        return $id ? (($this->studyProgramModel->find($id)['name'] ?? null)) : null;
    }

    private function storeEvidence($file, int $requestId, string $type, string $path): void
    {
        $stored = $file->getRandomName();
        $file->move($path, $stored);
        $this->evidenceModel->insert(['pengajuan_id' => $requestId, 'tipe_file' => $type, 'file_path' => 'bhp/' . basename($path) . '/' . $stored, 'original_name' => $file->getClientName(), 'uploaded_by' => auth()->id(), 'uploaded_at' => date('Y-m-d H:i:s')]);
    }
}
