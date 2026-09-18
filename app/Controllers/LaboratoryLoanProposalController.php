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

    private const STATUS_OPTIONS = ['draft', 'submitted', 'laboran_approved', 'rejected', 'approved', 'cancelled', 'completed'];

    public function index()
    {
        $tab = (string) $this->request->getGet('tab') === 'archive' ? 'archive' : 'active';
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $tabStatuses = $tab === 'archive'
            ? ['rejected', 'cancelled', 'completed']
            : array_values(array_diff(self::STATUS_OPTIONS, ['rejected', 'cancelled', 'completed']));
        $status = in_array($status, $tabStatuses, true) ? $status : '';
        $laboratoryUuid = trim((string) $this->request->getGet('laboratory_uuid'));
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 10;
        $canReview = activeGroupIs('superadmin', 'kepala_lab', 'laboran');
        $laboratoryOptions = $this->filterLaboratoryOptions();

        $query = $this->proposalModel
            ->select('laboratory_loan_proposals.*, users.username, GROUP_CONCAT(DISTINCT laboratories.name ORDER BY laboratories.name SEPARATOR ", ") AS laboratory_names')
            ->join('users', 'users.id = laboratory_loan_proposals.user_id')
            ->join('laboratory_loan_proposal_items', 'laboratory_loan_proposal_items.proposal_id = laboratory_loan_proposals.id', 'left')
            ->join('laboratories', 'laboratories.id = laboratory_loan_proposal_items.laboratory_id', 'left');

        if (! $canReview) {
            $query->where('laboratory_loan_proposals.user_id', auth()->id());
        }
        if (activeGroupIs('laboran')) {
            $assignedLaboratoryIds = $this->assignedLaboratoryIds();
            if (! empty($assignedLaboratoryIds)) {
                $query->whereIn('laboratory_loan_proposal_items.laboratory_id', $assignedLaboratoryIds);
            }
        }
        if ($laboratoryUuid !== '') {
            $query->where('laboratories.uuid', $laboratoryUuid);
        }
        if ($search !== '') {
            $query->groupStart()->like('identity_number', $search)->orLike('full_name', $search)->orLike('event_name', $search)->orLike('laboratories.name', $search)->orLike('status', $search)->groupEnd();
        }
        $query->whereIn('laboratory_loan_proposals.status', $tabStatuses);
        if ($status !== '') {
            $query->where('laboratory_loan_proposals.status', $status);
        }

        $proposals = $query->groupBy('laboratory_loan_proposals.id')->orderBy('laboratory_loan_proposals.proposal_date', 'DESC')->orderBy('laboratory_loan_proposals.id', 'DESC')->paginate($perPage);
        return $this->renderView('loan_proposals/index', [
            'title' => 'Peminjaman Laboratorium', 'page_title' => 'Peminjaman Laboratorium',
            'proposals' => $proposals, 'pager' => $this->proposalModel->pager, 'search' => $search,
            'status' => $status, 'statusOptions' => $tabStatuses,
            'laboratoryUuid' => $laboratoryUuid, 'laboratoryOptions' => $laboratoryOptions,
            'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $this->proposalModel->pager->getCurrentPage(), 'totalRows' => $this->proposalModel->pager->getTotal(),
            'canReview' => $canReview, 'tab' => $tab,
        ]);
    }

    public function approvalIndex()
    {
        $tab = (string) $this->request->getGet('tab') === 'history' ? 'history' : 'pending';
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $statusOptions = $tab === 'history'
            ? ['laboran_approved', 'approved', 'rejected', 'cancelled']
            : ['submitted', 'laboran_approved'];
        $status = in_array($status, $statusOptions, true) ? $status : '';
        $laboratoryUuid = trim((string) $this->request->getGet('laboratory_uuid'));
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 10;
        $isLaboran = activeGroupIs('laboran');
        $isKepalaLab = activeGroupIs('kepala_lab');
        $laboratoryOptions = $this->filterLaboratoryOptions();

        if ($tab === 'history') {
            $history = $this->statusHistoryModel->getApprovalHistory(
                $perPage,
                activeGroupIs('superadmin') ? null : (int) auth()->id(),
                $search,
                $status,
                $laboratoryUuid
            );

            return $this->renderView('loan_proposals/approval', [
                'title' => 'Persetujuan Peminjaman Laboratorium',
                'page_title' => 'Persetujuan Peminjaman Laboratorium',
                'proposals' => [],
                'history' => $history,
                'pager' => $this->statusHistoryModel->pager,
                'search' => $search,
                'status' => $status,
                'statusOptions' => $statusOptions,
                'laboratoryUuid' => $laboratoryUuid,
                'laboratoryOptions' => $laboratoryOptions,
                'perPage' => $perPage,
                'perPageOptions' => self::PER_PAGE_OPTIONS,
                'stage' => 'history',
                'tab' => $tab,
            ]);
        }

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

        if ($laboratoryUuid !== '') {
            $query->where('laboratories.uuid', $laboratoryUuid);
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

        $proposals = $query->groupBy('laboratory_loan_proposals.id')->orderBy('laboratory_loan_proposals.proposal_date', 'DESC')->orderBy('laboratory_loan_proposals.id', 'DESC')->paginate($perPage);

        return $this->renderView('loan_proposals/approval', [
            'title' => 'Persetujuan Peminjaman Laboratorium',
            'page_title' => 'Persetujuan Peminjaman Laboratorium',
            'proposals' => $proposals,
            'pager' => $this->proposalModel->pager,
            'search' => $search,
            'status' => $status,
            'statusOptions' => $statusOptions,
            'laboratoryUuid' => $laboratoryUuid,
            'laboratoryOptions' => $laboratoryOptions,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'stage' => $isLaboran ? 'laboran' : ($isKepalaLab ? 'kepala_lab' : 'all'),
            'history' => [],
            'tab' => $tab,
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
        notification()->sendProposalCompletedToApplicant(
            (int) $proposal['user_id'],
            (string) $proposal['event_name'],
            '/peminjaman/lab-loans/detail/' . $proposal['uuid']
        );

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal mengubah status proposal menjadi selesai.');
        }

        $db->transCommit();

        return $redirect->with('success', 'Proposal peminjaman berhasil ditandai selesai.');
    }

    public function cancel(string $uuid)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        $redirect = redirect()->to('/peminjaman/lab-loans');

        if (! $proposal) {
            return $redirect->with('error', 'Proposal peminjaman tidak ditemukan.');
        }

        if (! activeGroupIs('laboran') || ! $this->isAssignedLaboran((int) $proposal['id'])) {
            return $redirect->with('error', 'Hanya laboran yang ditugaskan pada laboratorium proposal ini yang dapat membatalkannya.');
        }

        $note = trim((string) $this->request->getPost('note'));
        if ($note === '') {
            return $redirect->with('error', 'Alasan pembatalan wajib diisi.');
        }

        $db = db_connect();
        $db->transBegin();
        $lockedProposal = $db->query(
            'SELECT id, status FROM laboratory_loan_proposals WHERE id = ? FOR UPDATE',
            [$proposal['id']]
        )->getRowArray();

        if (! $lockedProposal || $lockedProposal['status'] !== 'approved') {
            $db->transRollback();

            return $redirect->with('error', 'Hanya proposal yang sudah disetujui yang dapat dibatalkan.');
        }

        $this->proposalModel->update((int) $proposal['id'], ['status' => 'cancelled']);
        $this->statusHistoryModel->record((int) $proposal['id'], 'approved', 'cancelled', $note);
        notification()->sendProposalCancelledToApplicant(
            (int) $proposal['user_id'],
            (string) $proposal['event_name'],
            '/peminjaman/lab-loans/detail/' . $proposal['uuid'],
            $note
        );

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal membatalkan proposal peminjaman.');
        }

        $db->transCommit();

        return $redirect->with('success', 'Proposal peminjaman berhasil dibatalkan.');
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
        $this->proposalModel->insert($this->proposalData(date('Y-m-d H:i:s')));
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
        $this->proposalModel->update($proposal['id'], $this->proposalData($proposal['proposal_date']));
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

        $redirect = redirect()->to('/peminjaman/lab-loans');
        $db       = db_connect();

        helper('lab_availability');
        $db->transBegin();

        // Serialize submissions for the same proposal and laboratory rows.
        $lockedProposal = $db->query(
            'SELECT * FROM laboratory_loan_proposals WHERE id = ? FOR UPDATE',
            [$proposal['id']]
        )->getRowArray();

        if (! $lockedProposal || $lockedProposal['status'] !== 'draft') {
            $db->transRollback();

            return $redirect->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }

        $cart = $this->itemModel
            ->where('proposal_id', $lockedProposal['id'])
            ->findAll();

        if ($cart === []) {
            $db->transRollback();

            return redirect()->to('/peminjaman/lab-loans/items/' . $proposal['uuid'])->with('error', 'Tambahkan minimal satu ruangan sebelum mengajukan proposal.');
        }

        $laboratoryIds = array_values(array_unique(array_map(
            static fn (array $item): int => (int) $item['laboratory_id'],
            $cart
        )));
        $placeholders = implode(',', array_fill(0, count($laboratoryIds), '?'));

        // Lock laboratory rows so two proposals cannot pass this check concurrently.
        $db->query(
            'SELECT id FROM laboratories WHERE id IN (' . $placeholders . ') ORDER BY id FOR UPDATE',
            $laboratoryIds
        )->getResultArray();

        foreach ($laboratoryIds as $laboratoryId) {
            if (! lab_is_available(
                $laboratoryId,
                $lockedProposal['event_start'],
                $lockedProposal['event_end'],
                (int) $lockedProposal['id'],
                true
            )) {
                $db->transRollback();

                return $redirect->with('error', 'Laboratorium yang dipilih baru saja diproses atau dibooking pada rentang waktu kegiatan. Silakan pilih ruangan lain.');
            }
        }

        $this->proposalModel->update($lockedProposal['id'], ['status' => 'submitted']);
        $this->statusHistoryModel->record((int) $lockedProposal['id'], 'draft', 'submitted', 'Proposal diajukan untuk diproses.');

        notification()->sendProposalSubmittedToLaborans(
            (int) $lockedProposal['id'],
            (string) $lockedProposal['event_name'],
            '/peminjaman/lab-loans-approval'
        );

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal mengajukan proposal. Silakan coba lagi.');
        }

        $db->transCommit();

        return $redirect->with('success', 'Proposal peminjaman berhasil diajukan.');
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

    public function detailApproval(string $uuid)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        if (! $proposal || ! $this->canReviewProposal($proposal)) {
            return redirect()->to('/peminjaman/lab-loans-approval')->with('error', 'Proposal tidak tersedia untuk approval Anda.');
        }

        return $this->renderView('loan_proposals/detail', [
            'title' => 'Detail Approval Proposal', 'page_title' => 'Detail Approval Proposal',
            'proposal' => $proposal,
            'items' => $this->itemModel->getCart((int) $proposal['id']),
            'history' => $this->statusHistoryModel->getForProposal((int) $proposal['id']),
            'approvalMode' => true,
        ]);
    }

    public function detailApprovalHistory(string $uuid)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        if (! $proposal || ! $this->canViewApprovalHistory((int) $proposal['id'])) {
            return redirect()->to('/peminjaman/lab-loans-approval?tab=history')->with('error', 'Riwayat approval proposal tidak tersedia untuk Anda.');
        }

        return $this->renderView('loan_proposals/detail', [
            'title' => 'Detail Riwayat Approval', 'page_title' => 'Detail Riwayat Approval',
            'proposal' => $proposal,
            'items' => $this->itemModel->getCart((int) $proposal['id']),
            'history' => $this->statusHistoryModel->getForProposal((int) $proposal['id']),
            'approvalHistoryMode' => true,
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
            'proposal_date' => 'required|valid_date[Y-m-d\\TH:i]', 'event_name' => 'required|max_length[200]',
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

        notification()->sendProposalDecisionToApplicant(
            (int) $proposal['user_id'],
            (string) $proposal['event_name'],
            $approve,
            '/peminjaman/lab-loans/detail/' . $proposal['uuid'],
            $note
        );

        if ($approve && $nextStatus === 'laboran_approved') {
            notification()->sendApprovalNeededToHeadLab(
                (string) $proposal['event_name'],
                '/peminjaman/lab-loans-approval'
            );
        }

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal menyimpan keputusan approval.');
        }

        $db->transCommit();

        return $redirect->with('success', $approve ? 'Approval berhasil disimpan.' : 'Proposal berhasil ditolak.');
    }

    private function filterLaboratoryOptions(): array
    {
        $query = db_connect()->table('laboratories')
            ->select('laboratories.id, laboratories.uuid, laboratories.name, rooms.code AS room_code')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->where('laboratories.status', 'active')
            ->orderBy('laboratories.name', 'ASC');

        if (activeGroupIs('laboran')) {
            $assignedLaboratoryIds = $this->assignedLaboratoryIds();

            if (empty($assignedLaboratoryIds)) {
                return [];
            }

            $query->whereIn('laboratories.id', $assignedLaboratoryIds);
        }

        return $query->get()->getResultArray();
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

    private function canReviewProposal(array $proposal): bool
    {
        if (activeGroupIs('superadmin')) {
            return in_array($proposal['status'], ['submitted', 'laboran_approved'], true);
        }

        if (activeGroupIs('laboran')) {
            return $proposal['status'] === 'submitted' && $this->isAssignedLaboran((int) $proposal['id']);
        }

        return activeGroupIs('kepala_lab') && $proposal['status'] === 'laboran_approved';
    }

    private function canViewApprovalHistory(int $proposalId): bool
    {
        if (activeGroupIs('superadmin')) {
            return true;
        }

        return db_connect()->table('laboratory_loan_proposal_status_histories')
            ->where('proposal_id', $proposalId)
            ->where('changed_by', auth()->id())
            ->whereIn('to_status', ['laboran_approved', 'approved', 'rejected', 'cancelled'])
            ->countAllResults() > 0;
    }

    private function profileCompletionRedirect()
    {
        $user = auth()->user();

        if (trim((string) $user->username) !== '' && trim((string) $user->phone) !== '' && trim((string) $user->identity_number) !== '' && ! empty($user->study_program_id)) {
            return null;
        }

        return redirect()->to('/profile')->with('error', 'Lengkapi nama profil, nomor identitas, nomor HP, dan program studi sebelum mengajukan peminjaman laboratorium.');
    }

    private function proposalData(string $proposalDate): array
    {
        return [
            'user_id' => auth()->id(), 'identity_number' => trim((string) $this->request->getPost('identity_number')),
            'full_name' => trim((string) $this->request->getPost('full_name')), 'phone' => trim((string) $this->request->getPost('phone')),
            'email' => trim((string) $this->request->getPost('email')), 'proposal_date' => $proposalDate,
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