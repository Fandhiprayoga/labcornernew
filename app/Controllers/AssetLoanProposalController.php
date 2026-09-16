<?php

namespace App\Controllers;

use App\Models\AssetLoanProposalItemModel;
use App\Models\AssetLoanProposalModel;
use App\Models\AssetLoanProposalStatusHistoryModel;
use App\Models\AssetModel;

class AssetLoanProposalController extends BaseController
{
    private const PER_PAGE = [10, 25, 50, 100];
    private const CATALOG_PER_PAGE = [8, 12, 24, 48];
    private const STATUSES = ['draft', 'submitted', 'laboran_approved', 'rejected', 'approved', 'completed'];
    protected AssetLoanProposalModel $proposalModel;
    protected AssetLoanProposalItemModel $itemModel;
    protected AssetLoanProposalStatusHistoryModel $historyModel;

    public function __construct()
    {
        helper('asset_availability');
        $this->proposalModel = new AssetLoanProposalModel();
        $this->itemModel = new AssetLoanProposalItemModel();
        $this->historyModel = new AssetLoanProposalStatusHistoryModel();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $status = in_array($status, self::STATUSES, true) ? $status : '';
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : 10;
        $reviewer = activeGroupIs('superadmin', 'kepala_lab', 'laboran');
        $query = $this->proposalModel
            ->select('asset_loan_proposals.*, GROUP_CONCAT(DISTINCT CONCAT(assets.asset_code, " - ", assets.name) ORDER BY assets.asset_code SEPARATOR ", ") AS asset_names')
            ->join('asset_loan_proposal_items', 'asset_loan_proposal_items.proposal_id = asset_loan_proposals.id', 'left')
            ->join('assets', 'assets.id = asset_loan_proposal_items.asset_id', 'left');
        if (! $reviewer) $query->where('asset_loan_proposals.user_id', auth()->id());
        if ($search !== '') $query->groupStart()->like('asset_loan_proposals.identity_number', $search)->orLike('asset_loan_proposals.full_name', $search)->orLike('asset_loan_proposals.event_name', $search)->orLike('assets.asset_code', $search)->orLike('assets.name', $search)->groupEnd();
        if ($status !== '') $query->where('asset_loan_proposals.status', $status);
        $proposals = $query->groupBy('asset_loan_proposals.id')->orderBy('asset_loan_proposals.proposal_date', 'DESC')->orderBy('asset_loan_proposals.id', 'DESC')->paginate($perPage);
        return $this->renderView('asset_loan_proposals/index', [
            'title' => 'Peminjaman Asset', 'page_title' => 'Peminjaman Asset', 'proposals' => $proposals,
            'pager' => $this->proposalModel->pager, 'search' => $search, 'status' => $status,
            'statusOptions' => self::STATUSES, 'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE,
            'totalRows' => $this->proposalModel->pager->getTotal(),
        ]);
    }

    public function approvalIndex()
    {
        $tab = (string) $this->request->getGet('tab') === 'history' ? 'history' : 'pending';
        $search = trim((string) $this->request->getGet('q'));
        $statusOptions = $tab === 'history' ? ['laboran_approved', 'approved', 'rejected'] : ['submitted', 'laboran_approved'];
        $status = trim((string) $this->request->getGet('status'));
        $status = in_array($status, $statusOptions, true) ? $status : '';
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : 10;

        if ($tab === 'history') {
            $history = $this->historyModel->getApprovalHistory(
                $perPage,
                activeGroupIs('superadmin') ? null : (int) auth()->id(),
                $search,
                $status
            );

            return $this->renderView('asset_loan_proposals/approval', [
                'title' => 'Persetujuan Peminjaman Asset',
                'page_title' => 'Persetujuan Peminjaman Asset',
                'proposals' => [],
                'history' => $history,
                'pager' => $this->historyModel->pager,
                'search' => $search,
                'status' => $status,
                'statusOptions' => $statusOptions,
                'perPage' => $perPage,
                'perPageOptions' => self::PER_PAGE,
                'tab' => $tab,
            ]);
        }

        $query = $this->proposalModel
            ->select('asset_loan_proposals.*, GROUP_CONCAT(DISTINCT CONCAT(assets.asset_code, " - ", assets.name) ORDER BY assets.asset_code SEPARATOR ", ") AS asset_names')
            ->join('asset_loan_proposal_items', 'asset_loan_proposal_items.proposal_id = asset_loan_proposals.id')
            ->join('assets', 'assets.id = asset_loan_proposal_items.asset_id')
            ->join('laboratory_laborans', 'laboratory_laborans.laboratory_id = assets.laboratory_id', 'left');

        if (activeGroupIs('laboran')) {
            $query->where('asset_loan_proposals.status', 'submitted')
                ->where('laboratory_laborans.user_id', auth()->id());
        } elseif (activeGroupIs('kepala_lab')) {
            $query->where('asset_loan_proposals.status', 'laboran_approved');
        } else {
            $query->whereIn('asset_loan_proposals.status', ['submitted', 'laboran_approved']);
        }

        if ($search !== '') {
            $query->groupStart()
                ->like('asset_loan_proposals.identity_number', $search)
                ->orLike('asset_loan_proposals.full_name', $search)
                ->orLike('asset_loan_proposals.event_name', $search)
                ->orLike('assets.asset_code', $search)
                ->orLike('assets.name', $search)
                ->groupEnd();
        }

        if ($status !== '') $query->where('asset_loan_proposals.status', $status);

        $proposals = $query->groupBy('asset_loan_proposals.id')->orderBy('asset_loan_proposals.proposal_date', 'DESC')->orderBy('asset_loan_proposals.id', 'DESC')->paginate($perPage);

        return $this->renderView('asset_loan_proposals/approval', [
            'title' => 'Persetujuan Peminjaman Asset',
            'page_title' => 'Persetujuan Peminjaman Asset',
            'proposals' => $proposals,
            'history' => [],
            'pager' => $this->proposalModel->pager,
            'search' => $search,
            'status' => $status,
            'statusOptions' => $statusOptions,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'tab' => $tab,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->profileCompletionRedirect()) return $redirect;
        return $this->renderView('asset_loan_proposals/form', ['title' => 'Ajukan Peminjaman Asset', 'page_title' => 'Ajukan Peminjaman Asset', 'proposal' => null, 'user' => auth()->user()]);
    }

    public function store()
    {
        if ($redirect = $this->profileCompletionRedirect()) return $redirect;
        if (! $this->validateSubmission()) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        $this->proposalModel->insert($this->proposalData(date('Y-m-d H:i:s')));
        $this->historyModel->record((int) $this->proposalModel->getInsertID(), null, 'draft', 'Proposal dibuat.');
        return redirect()->to('/peminjaman/asset-loans')->with('success', 'Proposal peminjaman asset berhasil disimpan.');
    }

    public function edit(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        return $this->renderView('asset_loan_proposals/form', ['title' => 'Edit Proposal Peminjaman Asset', 'page_title' => 'Edit Proposal Peminjaman Asset', 'proposal' => $proposal, 'user' => auth()->user()]);
    }

    public function update(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        if (! $this->validateSubmission()) return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        $this->proposalModel->update($proposal['id'], $this->proposalData($proposal['proposal_date']));
        return redirect()->to('/peminjaman/asset-loans')->with('success', 'Proposal peminjaman asset berhasil diperbarui.');
    }

    public function delete(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        $this->proposalModel->delete($proposal['id']);
        return redirect()->to('/peminjaman/asset-loans')->with('success', 'Proposal peminjaman asset dibatalkan.');
    }

    public function items(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        $search = trim((string) $this->request->getGet('q'));
        $perPage = (int) $this->request->getGet('perPage');
        $perPage = in_array($perPage, self::CATALOG_PER_PAGE, true) ? $perPage : 12;
        $cart = $this->itemModel->getCart((int) $proposal['id']);
        $cartLaboratoryId = ! empty($cart) ? (int) $cart[0]['laboratory_id'] : null;
        $query = (new AssetModel())->select('assets.*, laboratories.name AS laboratory_name, rooms.code AS room_code')->join('laboratories', 'laboratories.id = assets.laboratory_id', 'left')->join('rooms', 'rooms.id = laboratories.room_id', 'left')->where('assets.status', AssetModel::STATUS_READY)->where('assets.can_be_borrowed', 1);
        if ($cartLaboratoryId !== null) $query->where('assets.laboratory_id', $cartLaboratoryId);
        $blockedAssetIds = asset_availability_blocked_ids($proposal['event_start'], $proposal['event_end'], (int) $proposal['id']);
        if ($blockedAssetIds !== []) $query->whereNotIn('assets.id', $blockedAssetIds);
        if ($search !== '') $query->groupStart()->like('assets.asset_code', $search)->orLike('assets.name', $search)->orLike('assets.category', $search)->orLike('assets.brand', $search)->groupEnd();
        $assets = $query->orderBy('assets.asset_code', 'ASC')->paginate($perPage);
        return $this->renderView('asset_loan_proposals/items', ['title' => 'Asset yang Dipinjam', 'page_title' => 'Asset yang Dipinjam', 'proposal' => $proposal, 'assets' => $assets, 'cart' => $cart, 'search' => $search, 'pager' => $query->pager, 'perPage' => $perPage, 'perPageOptions' => self::CATALOG_PER_PAGE, 'totalRows' => $query->pager->getTotal(), 'editable' => true]);
    }

    public function addItem(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        $redirect = redirect()->to('/peminjaman/asset-loans/items/' . $uuid);
        if (! $proposal || $proposal['status'] !== 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        $assetId = (int) $this->request->getPost('asset_id');
        $asset = (new AssetModel())->where(['id' => $assetId, 'status' => AssetModel::STATUS_READY, 'can_be_borrowed' => 1])->first();
        if (! $asset) return $redirect->with('error', 'Asset tidak ditemukan atau tidak dapat dipinjam.');
        if ($this->itemModel->where(['proposal_id' => $proposal['id'], 'asset_id' => $assetId])->first()) return $redirect->with('error', 'Asset tersebut sudah ada di cart.');
        $cart = $this->itemModel->getCart((int) $proposal['id']);
        if ($cart !== [] && (int) $asset['laboratory_id'] !== (int) $cart[0]['laboratory_id']) return $redirect->with('error', 'Asset harus berada di laboratorium yang sama dengan asset di cart.');
        if (! asset_is_available($assetId, $proposal['event_start'], $proposal['event_end'], (int) $proposal['id'])) return $redirect->with('error', 'Asset tersebut sudah dipakai pada rentang waktu kegiatan.');
        $notes = trim((string) $this->request->getPost('notes')) ?: null;
        $deletedItem = $this->itemModel->withDeleted()->where(['proposal_id' => $proposal['id'], 'asset_id' => $assetId])->first();

        if ($deletedItem && $deletedItem['deleted_at'] !== null) {
            db_connect()->table('asset_loan_proposal_items')
                ->where('id', $deletedItem['id'])
                ->update(['notes' => $notes, 'deleted_at' => null, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->itemModel->insert(['proposal_id' => $proposal['id'], 'asset_id' => $assetId, 'notes' => $notes]);
        }
        return $redirect->with('success', 'Asset ditambahkan ke cart.');
    }

    public function removeItem(string $uuid, string $itemUuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        $item = $this->itemModel->where(['uuid' => $itemUuid, 'proposal_id' => $proposal['id']])->first();
        if ($item) $this->itemModel->delete($item['id']);
        return redirect()->to('/peminjaman/asset-loans/items/' . $uuid)->with('success', 'Asset dihapus dari cart.');
    }

    public function confirm(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        $cart = $this->itemModel->getCart((int) $proposal['id']);
        if (! $cart) return redirect()->to('/peminjaman/asset-loans/items/' . $uuid)->with('error', 'Tambahkan minimal satu asset.');
        return $this->renderView('asset_loan_proposals/confirm', ['title' => 'Konfirmasi Peminjaman Asset', 'page_title' => 'Konfirmasi Peminjaman Asset', 'proposal' => $proposal, 'cart' => $cart]);
    }

    public function submit(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'draft') {
            return redirect()->to('/peminjaman/asset-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }

        $redirect = redirect()->to('/peminjaman/asset-loans');
        $db       = db_connect();

        $db->transBegin();

        // Kunci proposal agar dua submit pada proposal yang sama tidak berjalan bersamaan.
        $lockedProposal = $db->query(
            'SELECT * FROM asset_loan_proposals WHERE id = ? FOR UPDATE',
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

            return redirect()->to('/peminjaman/asset-loans/items/' . $proposal['uuid'])->with('error', 'Tambahkan minimal satu asset sebelum mengajukan proposal.');
        }

        $assetIds = array_values(array_unique(array_map(
            static fn (array $item): int => (int) $item['asset_id'],
            $cart
        )));
        $placeholders = implode(',', array_fill(0, count($assetIds), '?'));

        // Kunci asset agar dua proposal tidak lolos pengecekan pada waktu yang sama.
        $db->query(
            'SELECT id FROM assets WHERE id IN (' . $placeholders . ') ORDER BY id FOR UPDATE',
            $assetIds
        )->getResultArray();

        foreach ($assetIds as $assetId) {
            if (! asset_is_available(
                $assetId,
                $lockedProposal['event_start'],
                $lockedProposal['event_end'],
                (int) $lockedProposal['id']
            )) {
                $db->transRollback();

                return $redirect->with('error', 'Salah satu asset baru saja diproses atau dipinjam pada rentang waktu kegiatan.');
            }
        }

        $this->proposalModel->update($lockedProposal['id'], ['status' => 'submitted']);
        $this->historyModel->record((int) $lockedProposal['id'], 'draft', 'submitted', 'Proposal diajukan untuk diproses.');

        if ($db->transStatus() === false) {
            $db->transRollback();

            return $redirect->with('error', 'Gagal mengajukan proposal. Silakan coba lagi.');
        }

        $db->transCommit();

        notification()->sendAssetProposalSubmittedToReviewers(
            (int) $lockedProposal['id'],
            (string) $lockedProposal['event_name'],
            '/peminjaman/asset-loans-approval'
        );

        return $redirect->with('success', 'Proposal peminjaman asset berhasil diajukan.');
    }

    public function detail(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] === 'draft') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Detail tersedia setelah proposal diajukan.');
        return $this->renderView('asset_loan_proposals/detail', ['title' => 'Detail Proposal Asset', 'page_title' => 'Detail Proposal Asset', 'proposal' => $proposal, 'items' => $this->itemModel->getCart((int) $proposal['id']), 'history' => $this->historyModel->getForProposal((int) $proposal['id']), 'approvalMode' => false]);
    }

    public function approvalDetail(string $uuid)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        if (! $proposal || ! activeGroupCan('loans.approve') || ! $this->canReviewProposal($proposal)) return redirect()->to('/peminjaman/asset-loans-approval')->with('error', 'Proposal tidak tersedia untuk approval.');
        return $this->renderView('asset_loan_proposals/detail', ['title' => 'Approval Proposal Asset', 'page_title' => 'Approval Proposal Asset', 'proposal' => $proposal, 'items' => $this->itemModel->getCart((int) $proposal['id']), 'history' => $this->historyModel->getForProposal((int) $proposal['id']), 'approvalMode' => true]);
    }

    public function approve(string $uuid) { return $this->processApproval($uuid, true); }
    public function reject(string $uuid) { return $this->processApproval($uuid, false); }

    public function returnPage(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'approved') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Pengembalian hanya tersedia untuk proposal yang sudah disetujui.');

        return $this->renderView('asset_loan_proposals/return', [
            'title' => 'Pengembalian Asset',
            'page_title' => 'Pengembalian Asset',
            'proposal' => $proposal,
            'items' => $this->itemModel->getCart((int) $proposal['id']),
            'allReturned' => $this->itemModel->hasAllReturned((int) $proposal['id']),
        ]);
    }

    public function saveReturnStatus(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'approved') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Pengembalian hanya tersedia untuk proposal yang sudah disetujui.');

        $items = $this->itemModel->where('proposal_id', $proposal['id'])->findAll();
        $returnedIds = array_map('intval', (array) $this->request->getPost('returned') ?? []);
        $returnedMap = array_fill_keys($returnedIds, true);

        foreach ($items as $item) {
            $isReturned = isset($returnedMap[(int) $item['asset_id']]) ? 1 : 0;
            $payload = [
                'is_returned' => $isReturned,
                'returned_at' => $isReturned ? date('Y-m-d H:i:s') : null,
                'return_note' => $isReturned ? trim((string) ($this->request->getPost('return_note_' . $item['asset_id']) ?? '')) ?: null : null,
            ];

            $this->itemModel->update((int) $item['id'], $payload);
        }

        if ($this->itemModel->hasAllReturned((int) $proposal['id'])) {
            $this->proposalModel->update($proposal['id'], ['status' => 'completed']);
            $this->historyModel->record((int) $proposal['id'], 'approved', 'completed', 'Semua asset telah dikembalikan dan proposal ditandai selesai.');
            return redirect()->to('/peminjaman/asset-loans')->with('success', 'Semua asset telah dikembalikan. Proposal ditandai selesai.');
        }

        return redirect()->to('/peminjaman/asset-loans/returns/' . $uuid)->with('success', 'Status pengembalian barang berhasil disimpan. Tunggu semua asset dikembalikan sebelum proposal dinyatakan selesai.');
    }

    public function complete(string $uuid)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        if (! $proposal || $proposal['status'] !== 'approved') return redirect()->to('/peminjaman/asset-loans')->with('error', 'Hanya proposal yang disetujui yang dapat diselesaikan.');
        if (! $this->itemModel->hasAllReturned((int) $proposal['id'])) return redirect()->to('/peminjaman/asset-loans/returns/' . $uuid)->with('error', 'Semua asset harus dikembalikan terlebih dahulu sebelum proposal dapat ditandai selesai.');
        $this->proposalModel->update($proposal['id'], ['status' => 'completed']);
        $this->historyModel->record((int) $proposal['id'], 'approved', 'completed', 'Peminjaman ditandai selesai.');
        return redirect()->to('/peminjaman/asset-loans')->with('success', 'Proposal ditandai selesai.');
    }

    private function processApproval(string $uuid, bool $approve)
    {
        $proposal = $this->proposalModel->findByUuid($uuid);
        $redirect = redirect()->to('/peminjaman/asset-loans-approval');
        if (! $proposal || ! activeGroupCan('loans.approve')) return $redirect->with('error', 'Proposal tidak tersedia untuk approval.');

        $db = db_connect();
        $db->transBegin();
        $lockedProposal = $db->query('SELECT id, user_id, status, event_start, event_end, event_name, uuid FROM asset_loan_proposals WHERE id = ? FOR UPDATE', [$proposal['id']])->getRowArray();
        if (! $lockedProposal) {
            $db->transRollback();
            return $redirect->with('error', 'Proposal tidak ditemukan.');
        }

        $current = $lockedProposal['status'];
        $canApprove = activeGroupIs('superadmin')
            ? in_array($current, ['submitted', 'laboran_approved'], true)
            : (activeGroupIs('laboran')
                ? $current === 'submitted' && $this->isAssignedLaboran((int) $lockedProposal['id'])
                : activeGroupIs('kepala_lab') && $current === 'laboran_approved');
        if (! $canApprove) {
            $db->transRollback();
            return $redirect->with('error', 'Anda tidak dapat memproses proposal pada tahap ini.');
        }

        $note = trim((string) $this->request->getPost('note'));
        if (! $approve && $note === '') {
            $db->transRollback();
            return $redirect->with('error', 'Alasan penolakan wajib diisi.');
        }
        if ($approve) foreach ($this->itemModel->getCart((int) $lockedProposal['id']) as $item) if (! asset_is_available((int) $item['asset_id'], $lockedProposal['event_start'], $lockedProposal['event_end'], (int) $lockedProposal['id'])) {
            $db->transRollback();
            return $redirect->with('error', 'Asset tidak lagi tersedia pada rentang waktu kegiatan.');
        }

        $next = $approve ? ($current === 'submitted' ? 'laboran_approved' : 'approved') : 'rejected';
        $this->proposalModel->update($lockedProposal['id'], ['status' => $next]);
        $this->historyModel->record((int) $lockedProposal['id'], $current, $next, $note ?: 'Proposal disetujui.');
        if ($db->transStatus() === false) {
            $db->transRollback();
            return $redirect->with('error', 'Gagal menyimpan keputusan approval.');
        }
        $db->transCommit();

        if ($next === 'approved' || $next === 'rejected') {
            notification()->sendProposalDecisionToApplicant(
                (int) $lockedProposal['user_id'],
                (string) $lockedProposal['event_name'],
                $next === 'approved',
                '/peminjaman/asset-loans/detail/' . $lockedProposal['uuid'],
                $note,
                'asset_loan_proposal'
            );
        }

        return $redirect->with('success', $approve ? 'Approval berhasil disimpan.' : 'Proposal berhasil ditolak.');
    }

    private function validateSubmission(): bool
    {
        if (! $this->validate(['identity_number' => 'required|max_length[50]', 'full_name' => 'required|min_length[3]|max_length[150]', 'phone' => 'required|max_length[30]', 'email' => 'required|valid_email|max_length[150]', 'proposal_date' => 'required|valid_date[Y-m-d]', 'event_name' => 'required|max_length[200]', 'event_start' => 'required|valid_date[Y-m-d\\TH:i]', 'event_end' => 'required|valid_date[Y-m-d\\TH:i]', 'usage_location' => 'required|in_list[inside_lab,outside_lab]', 'acknowledgement' => 'required|in_list[1]'])) return false;
        $start = strtotime($this->normalizeDateTime($this->request->getPost('event_start'))); $end = strtotime($this->normalizeDateTime($this->request->getPost('event_end'))); $now = strtotime(date('Y-m-d H:i:00'));
        if ($start < $now) { $this->validator->setError('event_start', 'Waktu mulai tidak boleh backdate.'); return false; }
        if ($end <= $start) { $this->validator->setError('event_end', 'Waktu selesai harus setelah waktu mulai.'); return false; }
        return true;
    }

    private function proposalData(string $proposalDate): array
    {
        return ['user_id' => auth()->id(), 'identity_number' => trim((string) $this->request->getPost('identity_number')), 'full_name' => trim((string) $this->request->getPost('full_name')), 'phone' => trim((string) $this->request->getPost('phone')), 'email' => trim((string) $this->request->getPost('email')), 'proposal_date' => $proposalDate, 'event_name' => trim((string) $this->request->getPost('event_name')), 'event_start' => $this->normalizeDateTime($this->request->getPost('event_start')), 'event_end' => $this->normalizeDateTime($this->request->getPost('event_end')), 'usage_location' => $this->request->getPost('usage_location'), 'acknowledgement' => 1];
    }

    private function profileCompletionRedirect() { $user = auth()->user(); return trim((string) $user->username) !== '' && trim((string) $user->phone) !== '' && trim((string) $user->identity_number) !== '' && ! empty($user->study_program_id) ? null : redirect()->to('/profile')->with('error', 'Lengkapi nama profil, nomor identitas, nomor HP, dan program studi sebelum mengajukan peminjaman asset.'); }
    private function normalizeDateTime(?string $value): string { $value = str_replace('T', ' ', trim((string) $value)); return strlen($value) === 16 ? $value . ':00' : $value; }
    private function isAssignedLaboran(int $proposalId): bool
    {
        return db_connect()->table('asset_loan_proposal_items items')
            ->join('assets', 'assets.id = items.asset_id')
            ->join('laboratory_laborans assignments', 'assignments.laboratory_id = assets.laboratory_id')
            ->where('items.proposal_id', $proposalId)
            ->where('assignments.user_id', auth()->id())
            ->countAllResults() > 0;
    }

    private function canReviewProposal(array $proposal): bool
    {
        if (activeGroupIs('superadmin')) return in_array($proposal['status'], ['submitted', 'laboran_approved'], true);
        if (activeGroupIs('laboran')) return $proposal['status'] === 'submitted' && $this->isAssignedLaboran((int) $proposal['id']);
        return activeGroupIs('kepala_lab') && $proposal['status'] === 'laboran_approved';
    }

    private function findAccessible(string $uuid): ?array { $proposal = $this->proposalModel->findByUuid($uuid); if ($proposal && ! activeGroupIs('superadmin', 'kepala_lab', 'laboran') && (int) $proposal['user_id'] !== (int) auth()->id()) return null; return $proposal; }
}
