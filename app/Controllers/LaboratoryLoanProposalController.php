<?php

namespace App\Controllers;

use App\Models\LaboratoryLoanProposalItemModel;
use App\Models\LaboratoryLoanProposalStatusHistoryModel;
use App\Models\LaboratoryLoanProposalModel;
use App\Models\LaboratoryModel;

class LaboratoryLoanProposalController extends BaseController
{
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];
    private const CATALOG_PER_PAGE_OPTIONS = [8, 12, 24, 48];
    protected LaboratoryLoanProposalModel $proposalModel;
    protected LaboratoryLoanProposalItemModel $itemModel;
    protected LaboratoryLoanProposalStatusHistoryModel $statusHistoryModel;

    public function __construct()
    {
        $this->proposalModel = new LaboratoryLoanProposalModel();
        $this->itemModel     = new LaboratoryLoanProposalItemModel();
        $this->statusHistoryModel = new LaboratoryLoanProposalStatusHistoryModel();
    }

    private const STATUS_OPTIONS = ['draft', 'submitted', 'laboran_approved', 'rejected', 'approved', 'completed'];

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $status = in_array($status, self::STATUS_OPTIONS, true) ? $status : '';
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 10;
        $canReview = activeGroupIs('superadmin', 'kepala_lab', 'laboran');
        $query = $this->proposalModel->select('laboratory_loan_proposals.*, users.username')->join('users', 'users.id = laboratory_loan_proposals.user_id');

        if (! $canReview) {
            $query->where('laboratory_loan_proposals.user_id', auth()->id());
        }
        if ($search !== '') {
            $query->groupStart()->like('identity_number', $search)->orLike('full_name', $search)->orLike('event_name', $search)->orLike('status', $search)->groupEnd();
        }
        if ($status !== '') {
            $query->where('laboratory_loan_proposals.status', $status);
        }

        $proposals = $query->orderBy('proposal_date', 'DESC')->paginate($perPage);
        return $this->renderView('loan_proposals/index', [
            'title' => 'Peminjaman Laboratorium', 'page_title' => 'Peminjaman Laboratorium',
            'proposals' => $proposals, 'pager' => $this->proposalModel->pager, 'search' => $search,
            'status' => $status, 'statusOptions' => self::STATUS_OPTIONS,
            'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $this->proposalModel->pager->getCurrentPage(), 'totalRows' => $this->proposalModel->pager->getTotal(),
            'canReview' => $canReview,
        ]);
    }

    public function approvalIndex()
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $statusOptions = ['submitted', 'laboran_approved'];
        $status = in_array($status, $statusOptions, true) ? $status : '';
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 10;
        $isLaboran = activeGroupIs('laboran');
        $isKepalaLab = activeGroupIs('kepala_lab');

        $query = $this->proposalModel
            ->select('laboratory_loan_proposals.*, laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name')
            ->join('laboratory_loan_proposal_items', 'laboratory_loan_proposal_items.proposal_id = laboratory_loan_proposals.id')
            ->join('laboratories', 'laboratories.id = laboratory_loan_proposal_items.laboratory_id')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left');

        if ($isLaboran) {
            $query->where('laboratory_loan_proposals.status', 'submitted')
                ->whereIn('laboratory_loan_proposal_items.laboratory_id', $this->assignedLaboratoryIds());
        } elseif ($isKepalaLab) {
            $query->where('laboratory_loan_proposals.status', 'laboran_approved');
        } else {
            $query->whereIn('laboratory_loan_proposals.status', ['submitted', 'laboran_approved']);
        }

        if ($search !== '') {
            $query->groupStart()
                ->like('laboratory_loan_proposals.identity_number', $search)
                ->orLike('laboratory_loan_proposals.full_name', $search)
                ->orLike('laboratory_loan_proposals.event_name', $search)
                ->orLike('laboratories.name', $search)
                ->orLike('rooms.code', $search)
                ->orLike('rooms.name', $search)
                ->groupEnd();
        }

        if ($status !== '') {
            $query->where('laboratory_loan_proposals.status', $status);
        }

        $proposals = $query->orderBy('proposal_date', 'DESC')->paginate($perPage);

        return $this->renderView('loan_proposals/approval', [
            'title' => 'Persetujuan Peminjaman Laboratorium',
            'page_title' => 'Persetujuan Peminjaman Laboratorium',
            'proposals' => $proposals,
            'pager' => $this->proposalModel->pager,
            'search' => $search,
            'status' => $status,
            'statusOptions' => $statusOptions,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'stage' => $isLaboran ? 'laboran' : ($isKepalaLab ? 'kepala_lab' : 'all'),
        ]);
    }

    public function approve(string $uuid)
    {
        return $this->processApproval($uuid, true);
    }

    public function reject(string $uuid)
    {
        return $this->processApproval($uuid, false);
    }

    public function complete(string $uuid)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        $redirect = redirect()->to('/peminjaman/lab-loans');

        if (! $proposal) {
            return $redirect->with('error', 'Proposal peminjaman tidak ditemukan.');
        }

        if (! activeGroupIs('superadmin') && (! activeGroupIs('laboran') || ! $this->isAssignedLaboran((int) $proposal['id']))) {
            return $redirect->with('error', 'Anda tidak ditugaskan pada laboratorium proposal ini.');
        }

        $db = db_connect();
        $db->transBegin();
        $lockedProposal = $db->query(
            'SELECT id, status FROM laboratory_loan_proposals WHERE id = ? FOR UPDATE',
            [$proposal['id']]
        )->getRowArray();

        if (! $lockedProposal || $lockedProposal['status'] !== 'approved') {
            $db->transRollback();

            return $redirect->with('error', 'Hanya proposal yang sudah disetujui yang dapat ditandai selesai.');
        }

        $this->proposalModel->update((int) $proposal['id'], ['status' => 'completed']);
        $this->statusHistoryModel->record((int) $proposal['id'], 'approved', 'completed', 'Peminjaman ditandai selesai.');

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal mengubah status proposal menjadi selesai.');
        }

        $db->transCommit();

        return $redirect->with('success', 'Proposal peminjaman berhasil ditandai selesai.');
    }

    public function create()
    {
        if ($redirect = $this->profileCompletionRedirect()) {
            return $redirect;
        }

        return $this->renderView('loan_proposals/form', [
            'title' => 'Ajukan Proposal Peminjaman', 'page_title' => 'Ajukan Proposal Peminjaman',
            'proposal' => null, 'user' => auth()->user(),
        ]);
    }

    public function store()
    {
        if ($redirect = $this->profileCompletionRedirect()) {
            return $redirect;
        }

        if (! $this->validateSubmission()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->proposalModel->insert($this->proposalData());
        $this->statusHistoryModel->record((int) $this->proposalModel->getInsertID(), null, 'draft', 'Proposal dibuat.');
        return redirect()->to('/peminjaman/lab-loans')->with('success', 'Proposal peminjaman berhasil disimpan.');
    }

    public function edit(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }
        return $this->renderView('loan_proposals/form', [
            'title' => 'Edit Proposal Peminjaman', 'page_title' => 'Edit Proposal Peminjaman',
            'proposal' => $proposal, 'user' => auth()->user(),
        ]);
    }

    public function update(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }
        if (! $this->validateSubmission()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->proposalModel->update($proposal['id'], $this->proposalData());
        return redirect()->to('/peminjaman/lab-loans')->with('success', 'Proposal peminjaman berhasil diperbarui.');
    }

    public function delete(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }
        $this->proposalModel->delete($proposal['id']); 
        return redirect()->to('/peminjaman/lab-loans')->with('success', 'Proposal peminjaman berhasil dibatalkan.');
    }

    public function submit(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }

        if ($this->itemModel->where('proposal_id', $proposal['id'])->countAllResults() < 1) {
            return redirect()->to('/peminjaman/lab-loans/items/' . $proposal['uuid'])->with('error', 'Tambahkan minimal satu ruangan sebelum mengajukan proposal.');
        }

        $this->proposalModel->update($proposal['id'], ['status' => 'submitted']);
    $this->statusHistoryModel->record((int) $proposal['id'], 'draft', 'submitted', 'Proposal diajukan untuk diproses.');

        return redirect()->to('/peminjaman/lab-loans')->with('success', 'Proposal peminjaman berhasil diajukan.');
    }

    public function confirm(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }

        $cart = $this->itemModel->getCart((int) $proposal['id']);
        if (empty($cart)) {
            return redirect()->to('/peminjaman/lab-loans/items/' . $proposal['uuid'])->with('error', 'Tambahkan minimal satu ruangan sebelum melanjutkan.');
        }

        return $this->renderView('loan_proposals/confirm', [
            'title' => 'Konfirmasi Proposal Peminjaman', 'page_title' => 'Konfirmasi Proposal Peminjaman',
            'proposal' => $proposal, 'cart' => $cart,
        ]);
    }

    /**
     * Halaman detail peminjaman: katalog ruangan laboratorium + cart pilihan.
     */
    public function items(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }

        helper('lab_availability');

        $search  = trim((string) $this->request->getGet('q'));
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::CATALOG_PER_PAGE_OPTIONS, true) ? $perPage : 12;

        // Katalog hanya menampilkan laboratorium yang bebas bentrok pada rentang kegiatan proposal.
        $availableIds = lab_available_ids($proposal['event_start'], $proposal['event_end'], (int) $proposal['id']);

        $laboratoryModel = new LaboratoryModel();
        $query           = $laboratoryModel
            ->select('laboratories.id, laboratories.uuid, laboratories.name, laboratories.photo, laboratories.description, rooms.code AS room_code, rooms.name AS room_name, rooms.building, rooms.floor, rooms.capacity')
            ->join('rooms', 'rooms.id = laboratories.room_id')
            ->where('laboratories.status', 'active')
            ->whereIn('laboratories.id', $availableIds ?: [0]);

        if ($search !== '') {
            $query->groupStart()
                ->like('laboratories.name', $search)
                ->orLike('rooms.code', $search)
                ->orLike('rooms.name', $search)
                ->orLike('rooms.building', $search)
                ->groupEnd();
        }

        $laboratories = $query->orderBy('laboratories.name', 'ASC')->paginate($perPage);

        return $this->renderView('loan_proposals/items', [
            'title' => 'Item Peminjaman', 'page_title' => 'Item Peminjaman',
            'proposal' => $proposal, 'laboratories' => $laboratories,
            'cart' => $this->itemModel->getCart((int) $proposal['id']),
            'search' => $search,
            'pager' => $laboratoryModel->pager, 'perPage' => $perPage,
            'perPageOptions' => self::CATALOG_PER_PAGE_OPTIONS,
            'totalRows' => $laboratoryModel->pager->getTotal(),
            'currentPage' => $laboratoryModel->pager->getCurrentPage(),
            'editable' => $proposal['status'] === 'draft' && activeGroupCan('loans.edit'),
        ]);
    }

    public function detail(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] === 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Detail hanya tersedia untuk proposal yang sudah diajukan.');
        }

        return $this->renderView('loan_proposals/detail', [
            'title' => 'Detail Proposal Peminjaman', 'page_title' => 'Detail Proposal Peminjaman',
            'proposal' => $proposal,
            'items' => $this->itemModel->getCart((int) $proposal['id']),
            'history' => $this->statusHistoryModel->getForProposal((int) $proposal['id']),
        ]);
    }

    public function addItem(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }

        $redirect     = redirect()->to('/peminjaman/lab-loans/items/' . $proposal['uuid']);
        $laboratoryId = (int) $this->request->getPost('laboratory_id');
        $laboratory   = (new LaboratoryModel())->where('status', 'active')->find($laboratoryId);

        if (! $laboratory) {
            return $redirect->with('error', 'Laboratorium tidak ditemukan atau sedang nonaktif.');
        }

        $exists = $this->itemModel->where('proposal_id', $proposal['id'])->where('laboratory_id', $laboratoryId)->first();
        if ($exists) {
            return $redirect->with('error', 'Laboratorium tersebut sudah ada di dalam cart.');
        }

        helper('lab_availability');
        $db = db_connect();
        $db->transBegin();

        // Lock the proposal so concurrent requests cannot add a second laboratory.
        $db->query(
            'SELECT id FROM laboratory_loan_proposals WHERE id = ? FOR UPDATE',
            [$proposal['id']]
        )->getRowArray();

        if ($this->itemModel->where('proposal_id', $proposal['id'])->countAllResults() >= 1) {
            $db->transRollback();

            return $redirect->with('error', 'Satu proposal hanya dapat memiliki satu laboratorium.');
        }

        // Dicek ulang dengan locking read agar tidak double booking bila ada user lain memilih ruangan yang sama bersamaan.
        if (! lab_is_available($laboratoryId, $proposal['event_start'], $proposal['event_end'], (int) $proposal['id'], true)) {
            $db->transRollback();

            return $redirect->with('error', 'Laboratorium tersebut baru saja dibooking pada rentang waktu kegiatan Anda. Silakan pilih ruangan lain.');
        }

        $this->itemModel->insert([
            'proposal_id'   => $proposal['id'],
            'laboratory_id' => $laboratoryId,
            'notes'         => trim((string) $this->request->getPost('notes')) ?: null,
        ]);

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal menambahkan laboratorium ke cart. Silakan coba lagi.');
        }

        $db->transCommit();

        return $redirect->with('success', 'Laboratorium ditambahkan ke cart peminjaman.');
    }

    public function removeItem(string $uuid, string $itemUuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }

        $redirect = redirect()->to('/peminjaman/lab-loans/items/' . $proposal['uuid']);
        $item     = $this->itemModel->where('uuid', $itemUuid)->where('proposal_id', $proposal['id'])->first();

        if (! $item) {
            return $redirect->with('error', 'Item tidak ditemukan.');
        }

        $this->itemModel->delete($item['id']);

        return $redirect->with('success', 'Laboratorium dihapus dari cart peminjaman.');
    }

    private function validateSubmission(): bool
    {
        if (! $this->validate([
            'identity_number' => 'required|max_length[50]', 'full_name' => 'required|min_length[3]|max_length[150]',
            'phone' => 'required|max_length[30]', 'email' => 'required|valid_email|max_length[150]',
            'proposal_date' => 'required|valid_date[Y-m-d]', 'event_name' => 'required|max_length[200]',
            'event_start' => 'required|valid_date[Y-m-d\TH:i]', 'event_end' => 'required|valid_date[Y-m-d\TH:i]',
            'acknowledgement' => 'required|in_list[1]',
        ])) {
            return false;
        }

        $eventStart = strtotime($this->normalizeDateTime($this->request->getPost('event_start')));
        $eventEnd   = strtotime($this->normalizeDateTime($this->request->getPost('event_end')));
        $now        = strtotime(date('Y-m-d H:i:00'));

        if ($eventStart < $now) {
            $this->validator->setError('event_start', 'Waktu mulai tidak boleh backdate.');
            return false;
        }

        if ($eventEnd < $now) {
            $this->validator->setError('event_end', 'Waktu selesai tidak boleh backdate.');
            return false;
        }

        if ($eventEnd <= $eventStart) {
            $this->validator->setError('event_end', 'Waktu selesai harus setelah waktu mulai.');
            return false;
        }

        return true;
    }

    private function processApproval(string $uuid, bool $approve)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        $redirect = redirect()->to('/peminjaman/lab-loans-approval');

        if (! $proposal) {
            return $redirect->with('error', 'Proposal peminjaman tidak ditemukan.');
        }

        $db = db_connect();
        $db->transBegin();
        $lockedProposal = $db->query(
            'SELECT id, status FROM laboratory_loan_proposals WHERE id = ? FOR UPDATE',
            [$proposal['id']]
        )->getRowArray();

        if (! $lockedProposal) {
            $db->transRollback();

            return $redirect->with('error', 'Proposal peminjaman tidak ditemukan.');
        }

        $currentStatus = $lockedProposal['status'];
        $canApprove = false;
        if (activeGroupIs('laboran')) {
            $canApprove = $currentStatus === 'submitted' && $this->isAssignedLaboran((int) $proposal['id']);
        } elseif (activeGroupIs('kepala_lab')) {
            $canApprove = $currentStatus === 'laboran_approved';
        } elseif (activeGroupIs('superadmin')) {
            $canApprove = in_array($currentStatus, ['submitted', 'laboran_approved'], true);
        }

        if (! $canApprove) {
            $db->transRollback();

            return $redirect->with('error', 'Anda tidak dapat memproses proposal pada tahap ini.');
        }

        $note = trim((string) $this->request->getPost('note'));

        if (! $approve && $note === '') {
            $db->transRollback();

            return $redirect->with('error', 'Alasan penolakan wajib diisi.');
        }

        $nextStatus = $approve
            ? ($currentStatus === 'submitted' ? 'laboran_approved' : 'approved')
            : 'rejected';
        $note = $note !== '' ? $note : 'Proposal disetujui.';

        $this->proposalModel->update((int) $proposal['id'], ['status' => $nextStatus]);
        $this->statusHistoryModel->record((int) $proposal['id'], $currentStatus, $nextStatus, $note);

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal menyimpan keputusan approval.');
        }

        $db->transCommit();

        return $redirect->with('success', $approve ? 'Approval berhasil disimpan.' : 'Proposal berhasil ditolak.');
    }

    private function assignedLaboratoryIds(): array
    {
        $ids = db_connect()->table('laboratory_laborans')
            ->select('laboratory_id')
            ->where('user_id', auth()->id())
            ->get()
            ->getResultArray();

        return $ids ? array_map(static fn (array $row): int => (int) $row['laboratory_id'], $ids) : [0];
    }

    private function isAssignedLaboran(int $proposalId): bool
    {
        return db_connect()->table('laboratory_loan_proposal_items items')
            ->join('laboratory_laborans assignments', 'assignments.laboratory_id = items.laboratory_id')
            ->where('items.proposal_id', $proposalId)
            ->where('assignments.user_id', auth()->id())
            ->countAllResults() > 0;
    }

    private function profileCompletionRedirect()
    {
        $user = auth()->user();

        if (trim((string) $user->username) !== '' && trim((string) $user->phone) !== '') {
            return null;
        }

        return redirect()->to('/profile')->with('error', 'Lengkapi nama profil dan nomor HP sebelum mengajukan peminjaman laboratorium.');
    }

    private function proposalData(): array
    {
        return [
            'user_id' => auth()->id(), 'identity_number' => trim((string) $this->request->getPost('identity_number')),
            'full_name' => trim((string) $this->request->getPost('full_name')), 'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')), 'proposal_date' => $this->request->getPost('proposal_date'),
            'event_name' => trim((string) $this->request->getPost('event_name')),
            'event_start' => $this->normalizeDateTime($this->request->getPost('event_start')),
            'event_end' => $this->normalizeDateTime($this->request->getPost('event_end')),
            'acknowledgement' => 1,
        ];
    }

    private function normalizeDateTime(?string $value): string
    {
        $value = str_replace('T', ' ', trim((string) $value));
        return strlen($value) === 16 ? $value . ':00' : $value;
    }

    private function findAccessible(string $uuid): ?array
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        if ($proposal && ! activeGroupIs('superadmin', 'kepala_lab', 'laboran') && (int) $proposal['user_id'] !== (int) auth()->id()) {
            return null;
        }
        return $proposal;
    }
}