<?php

namespace App\Controllers;

use App\Models\BhpAuditLogModel;
use App\Models\BhpEvidenceModel;
use App\Models\BhpItemModel;
use App\Models\BhpPeriodModel;
use App\Models\BhpRequestModel;
use App\Models\LaboratoryModel;
use App\Models\StudyProgramModel;

class BhpController extends BaseController
{
    private const STATUSES = ['DRAFT', 'PENDING_REVIEW', 'NEED_REVISION', 'APPROVED_BY_KALAB', 'FUND_DISBURSED', 'EVIDEN_SUBMITTED', 'COMPLETED', 'REJECTED'];
    private const UNITS = ['Pcs', 'Box', 'Roll', 'Liter', 'Rim', 'Bottle', 'Pack', 'Unit', 'Custom'];

    protected BhpRequestModel $requestModel;
    protected BhpItemModel $itemModel;
    protected BhpPeriodModel $periodModel;
    protected BhpEvidenceModel $evidenceModel;
    protected BhpAuditLogModel $auditModel;
    protected LaboratoryModel $laboratoryModel;
    protected StudyProgramModel $studyProgramModel;

    public function __construct()
    {
        $this->requestModel = new BhpRequestModel();
        $this->itemModel = new BhpItemModel();
        $this->periodModel = new BhpPeriodModel();
        $this->evidenceModel = new BhpEvidenceModel();
        $this->auditModel = new BhpAuditLogModel();
        $this->laboratoryModel = new LaboratoryModel();
        $this->studyProgramModel = new StudyProgramModel();
    }

    public function index()
    {
        $query = $this->requestModel
            ->select('pengajuan_bhp.*, users.username, laboratories.name AS laboratory_name, study_programs.name AS study_program_name, periode_pengajuan.nama_periode')
            ->join('users', 'users.id = pengajuan_bhp.laboran_id')
            ->join('laboratories', 'laboratories.id = pengajuan_bhp.laboratory_id')
            ->join('study_programs', 'study_programs.id = pengajuan_bhp.study_program_id', 'left')
            ->join('periode_pengajuan', 'periode_pengajuan.id = pengajuan_bhp.periode_id');

        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        if (in_array(activeGroup(), ['laboran', 'user'], true)) {
            $query->where('pengajuan_bhp.laboran_id', auth()->id());
        }
        if ($status !== '' && in_array($status, self::STATUSES, true)) {
            $query->where('pengajuan_bhp.status', $status);
        }
        if ($search !== '') {
            $query->groupStart()->like('pengajuan_bhp.kode_pengajuan', $search)->orLike('laboratories.name', $search)->orLike('users.username', $search)->orLike('pengajuan_bhp.status', $search)->groupEnd();
        }

        $requests = $query->orderBy('pengajuan_bhp.created_at', 'DESC')->paginate(15);
        return $this->renderView('bhp/index', [
            'title' => 'Pengajuan BHP', 'page_title' => 'Pengajuan Bahan Habis Pakai',
            'requests' => $requests, 'pager' => $this->requestModel->pager,
            'search' => $search, 'status' => $status, 'statuses' => self::STATUSES,
            'periods' => $this->periodModel->orderBy('tanggal_mulai', 'DESC')->findAll(),
        ]);
    }

    public function create()
    {
        $period = $this->periodModel->active();
        if (! $period && ! activeGroupIs('superadmin')) {
            return redirect()->to('/bhp')->with('error', 'Jendela pengajuan sedang ditutup.');
        }
        return $this->renderView('bhp/form', [
            'title' => 'Buat Pengajuan BHP', 'page_title' => 'Buat Pengajuan BHP', 'requestData' => null,
            'period' => $period, 'laboratories' => $this->availableLaboratories(),
            'periods' => activeGroupIs('superadmin') ? $this->periodModel->orderBy('tanggal_mulai', 'DESC')->findAll() : ($period ? [$period] : []),
            'studyPrograms' => $this->studyProgramModel->orderBy('name')->findAll(), 'units' => self::UNITS,
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
        $laboratory = $this->laboratoryModel->find((int) $data['laboratory_id']);
        if (! $laboratory || (! activeGroupIs('superadmin', 'kepala_lab') && ! $this->isAssignedLaboratory((int) $data['laboratory_id']))) {
            return redirect()->back()->withInput()->with('error', 'Laboratorium tidak tersedia untuk grup aktif Anda.');
        }
        $period = $this->periodModel->find((int) $data['periode_id']);
        if (! $period || (! activeGroupIs('superadmin') && ! $this->periodModel->active())) {
            return redirect()->back()->withInput()->with('error', 'Periode pengajuan tidak aktif.');
        }
        $items = $this->postedItems();
        if (empty($items)) {
            return redirect()->back()->withInput()->with('error', 'Tambahkan minimal satu item BHP.');
        }
        $db = db_connect();
        $db->transStart();
        $this->requestModel->insert([
            'kode_pengajuan' => $this->nextCode($laboratory['name'], $period['id']),
            'periode_id' => $period['id'], 'laboran_id' => auth()->id(), 'laboratory_id' => $laboratory['id'],
            'study_program_id' => $data['study_program_id'] ?: null, 'nama_lab_snapshot' => $laboratory['name'],
            'prodi_snapshot' => $this->studyProgramName((int) $data['study_program_id']), 'grand_total_estimasi' => 0, 'status' => 'DRAFT',
        ]);
        $requestId = (int) $this->requestModel->getInsertID();
        $total = 0;
        foreach ($items as $item) {
            $total += $item['total_harga'];
            $item['pengajuan_id'] = $requestId;
            $this->itemModel->insert($item);
        }
        $this->requestModel->update($requestId, ['grand_total_estimasi' => $total]);
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
        return $this->renderView('bhp/form', [
            'title' => 'Edit Pengajuan BHP', 'page_title' => 'Edit Pengajuan BHP', 'requestData' => $requestData,
            'period' => $this->periodModel->find($requestData['periode_id']), 'laboratories' => $this->availableLaboratories(),
            'periods' => [$this->periodModel->find($requestData['periode_id'])],
            'studyPrograms' => $this->studyProgramModel->orderBy('name')->findAll(), 'units' => self::UNITS,
            'items' => $this->itemModel->where('pengajuan_id', $requestData['id'])->findAll(),
        ]);
    }

    public function update(string $uuid)
    {
        $requestData = $this->accessible($uuid);
        if (! $requestData || ! in_array($requestData['status'], ['DRAFT', 'NEED_REVISION'], true)) {
            return redirect()->to('/bhp')->with('error', 'Pengajuan tidak dapat diedit.');
        }
        $data = $this->requestData();
        if (! $this->validateData($data, $this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $laboratory = $this->laboratoryModel->find($data['laboratory_id']);
        if (! $laboratory || (! activeGroupIs('superadmin', 'kepala_lab') && ! $this->isAssignedLaboratory($data['laboratory_id']))) {
            return redirect()->back()->withInput()->with('error', 'Laboratorium tidak tersedia untuk grup aktif Anda.');
        }
        $items = $this->postedItems();
        if (empty($items)) return redirect()->back()->withInput()->with('error', 'Tambahkan minimal satu item BHP.');
        $db = db_connect();
        $db->transStart();
        $this->itemModel->where('pengajuan_id', $requestData['id'])->delete(null, true);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['total_harga'];
            $item['pengajuan_id'] = $requestData['id'];
            $this->itemModel->insert($item);
        }
        $this->requestModel->update($requestData['id'], ['laboratory_id' => $laboratory['id'], 'study_program_id' => $data['study_program_id'] ?: null, 'nama_lab_snapshot' => $laboratory['name'], 'prodi_snapshot' => $this->studyProgramName($data['study_program_id']), 'grand_total_estimasi' => $total, 'catatan_revisi' => null]);
        $db->transComplete();
        return $db->transStatus() ? redirect()->to('/bhp/detail/' . $uuid)->with('success', 'Pengajuan BHP berhasil diperbarui.') : redirect()->back()->withInput()->with('error', 'Pengajuan BHP gagal diperbarui.');
    }

    public function submit(string $uuid)
    {
        $requestData = $this->accessible($uuid);
        if (! $requestData || ! in_array($requestData['status'], ['DRAFT', 'NEED_REVISION'], true)) {
            return redirect()->to('/bhp')->with('error', 'Hanya draft atau pengajuan revisi yang dapat dikirim.');
        }
        $period = $this->periodModel->find($requestData['periode_id']);
        if (! activeGroupIs('superadmin') && (! $period || ! $this->periodIsActive($period))) {
            return redirect()->to('/bhp')->with('error', 'Jendela pengajuan sedang ditutup.');
        }
        $items = $this->itemModel->where('pengajuan_id', $requestData['id'])->findAll();
        if (empty($items)) {
            return redirect()->to('/bhp')->with('error', 'Pengajuan harus memiliki minimal satu item.');
        }
        $this->transition($requestData, 'PENDING_REVIEW', 'Pengajuan dikirim untuk ditinjau.');
        if (activeGroupIs('superadmin') && ! $this->periodIsActive($period)) {
            $this->auditModel->insert(['admin_id' => auth()->id(), 'action_type' => 'OVERRIDE_WINDOW', 'target_entity_id' => $requestData['id'], 'notes' => 'Submit di luar periode aktif.']);
        }
        return redirect()->to('/bhp')->with('success', 'Pengajuan BHP berhasil dikirim.');
    }

    public function approvalIndex()
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $query = $this->requestModel->select('pengajuan_bhp.*, laboratories.name AS laboratory_name')
            ->join('laboratories', 'laboratories.id = pengajuan_bhp.laboratory_id')
            ->whereIn('pengajuan_bhp.status', ['PENDING_REVIEW', 'EVIDEN_SUBMITTED']);
        if (in_array($status, ['PENDING_REVIEW', 'EVIDEN_SUBMITTED'], true)) $query->where('pengajuan_bhp.status', $status);
        else $status = '';
        if ($search !== '') $query->groupStart()->like('pengajuan_bhp.kode_pengajuan', $search)->orLike('laboratories.name', $search)->orLike('pengajuan_bhp.nama_lab_snapshot', $search)->groupEnd();
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
        return $this->renderView('bhp/detail', ['title' => 'Detail Pengajuan BHP', 'page_title' => $data['kode_pengajuan'], 'requestData' => $data, 'items' => $this->itemModel->where('pengajuan_id', $data['id'])->findAll(), 'evidences' => $this->evidenceModel->where('pengajuan_id', $data['id'])->findAll(), 'history' => (new \App\Models\BhpStatusHistoryModel())->where('pengajuan_id', $data['id'])->orderBy('id', 'DESC')->findAll()]);
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
        $now = date('Y-m-d H:i:s');
        $query = $this->periodModel->orderBy('tanggal_mulai', 'DESC');
        if ($tab === 'archive') {
            $query->where('tanggal_selesai <', $now);
        } else {
            $query->where('tanggal_mulai <=', $now)->where('tanggal_selesai >=', $now);
        }

        return $this->renderView('bhp/periods', [
            'title' => 'Periode Pengajuan BHP',
            'page_title' => 'Periode Pengajuan BHP',
            'periods' => $query->findAll(),
            'tab' => $tab,
        ]);
    }

    public function createPeriod()
    {
        return $this->renderView('bhp/period_form', ['title' => 'Buat Periode Pengajuan BHP', 'page_title' => 'Buat Periode Pengajuan BHP']);
    }

    public function storePeriod()
    {
        $data = $this->request->getPost();
        if (! $this->validateData($data, ['nama_periode' => 'required|max_length[100]', 'tanggal_mulai' => 'required|valid_date[Y-m-d\\TH:i]', 'tanggal_selesai' => 'required|valid_date[Y-m-d\\TH:i]'])) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        if (strtotime($data['tanggal_selesai']) <= strtotime($data['tanggal_mulai'])) return redirect()->back()->withInput()->with('error', 'Tanggal selesai harus setelah tanggal mulai.');
        $overlap = $this->periodModel->where('tanggal_mulai <', date('Y-m-d H:i:s', strtotime($data['tanggal_selesai'])))->where('tanggal_selesai >', date('Y-m-d H:i:s', strtotime($data['tanggal_mulai'])))->first();
        if ($overlap) return redirect()->back()->withInput()->with('error', 'Periode tidak boleh overlap secara global.');
        $this->periodModel->insert(['nama_periode' => $data['nama_periode'], 'tanggal_mulai' => date('Y-m-d H:i:s', strtotime($data['tanggal_mulai'])), 'tanggal_selesai' => date('Y-m-d H:i:s', strtotime($data['tanggal_selesai'])), 'created_by' => auth()->id()]);
        return redirect()->to('/bhp/periods')->with('success', 'Periode berhasil dibuat.');
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
        if (activeGroupIs('laboran', 'user') && (int) $data['laboran_id'] !== (int) auth()->id()) return null;
        return $data;
    }

    private function availableLaboratories(): array
    {
        if (! activeGroupIs('laboran')) return $this->laboratoryModel->where('status', 'active')->orderBy('name')->findAll();
        return $this->laboratoryModel->select('laboratories.*')->join('laboratory_laborans', 'laboratory_laborans.laboratory_id = laboratories.id')->where('laboratory_laborans.user_id', auth()->id())->where('laboratories.status', 'active')->orderBy('name')->findAll();
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
        return ['periode_id' => (int) $this->request->getPost('periode_id'), 'laboratory_id' => (int) $this->request->getPost('laboratory_id'), 'study_program_id' => (int) $this->request->getPost('study_program_id')];
    }

    private function rules(): array
    {
        return ['periode_id' => 'required|is_natural_no_zero', 'laboratory_id' => 'required|is_natural_no_zero', 'study_program_id' => 'permit_empty|is_natural'];
    }

    private function postedItems(): array
    {
        $names = $this->request->getPost('nama_barang') ?? [];
        $items = [];
        foreach ($names as $i => $name) {
            $name = trim((string) $name);
            $qty = (int) ($this->request->getPost('qty')[$i] ?? 0);
            $price = (float) ($this->request->getPost('harga_satuan')[$i] ?? 0);
            if ($name === '' || $qty < 1 || $price < 0) continue;
            $items[] = ['nama_barang' => $name, 'spesifikasi' => trim((string) (($this->request->getPost('spesifikasi')[$i] ?? ''))), 'qty' => $qty, 'satuan' => (string) (($this->request->getPost('satuan')[$i] ?? 'Unit')), 'harga_satuan' => $price, 'total_harga' => $qty * $price, 'vendor' => trim((string) (($this->request->getPost('vendor')[$i] ?? ''))), 'link_toko_online' => trim((string) (($this->request->getPost('link_toko_online')[$i] ?? '')))];
        }
        return $items;
    }

    private function nextCode(string $lab, int $periodId): string
    {
        $prefix = 'BHP/' . strtoupper(preg_replace('/[^A-Za-z0-9]+/', '-', $lab)) . '/' . date('Y/m');
        $count = $this->requestModel->like('kode_pengajuan', $prefix . '/', 'after')->where('periode_id', $periodId)->countAllResults() + 1;
        return $prefix . '/' . str_pad((string) $count, 3, '0', STR_PAD_LEFT);
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
