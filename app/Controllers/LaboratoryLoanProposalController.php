<?php

namespace App\Controllers;

use App\Models\LaboratoryLoanProposalModel;

class LaboratoryLoanProposalController extends BaseController
{
    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];
    protected LaboratoryLoanProposalModel $proposalModel;

    public function __construct()
    {
        $this->proposalModel = new LaboratoryLoanProposalModel();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
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

        $proposals = $query->orderBy('proposal_date', 'DESC')->paginate($perPage);
        return $this->renderView('loan_proposals/index', [
            'title' => 'Peminjaman Laboratorium', 'page_title' => 'Peminjaman Laboratorium',
            'proposals' => $proposals, 'pager' => $this->proposalModel->pager, 'search' => $search,
            'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $this->proposalModel->pager->getCurrentPage(), 'totalRows' => $this->proposalModel->pager->getTotal(),
            'canReview' => $canReview,
        ]);
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
        return redirect()->to('/peminjaman/lab-loans')->with('success', 'Proposal peminjaman berhasil diajukan.');
    }

    public function edit(string $uuid)
    {
        $proposal = $this->findAccessible($uuid);
        if (! $proposal || $proposal['status'] !== 'submitted') {
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
        if (! $proposal || $proposal['status'] !== 'submitted') {
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
        if (! $proposal || $proposal['status'] !== 'submitted') {
            return redirect()->to('/peminjaman/lab-loans')->with('error', 'Proposal tidak ditemukan atau sudah diproses.');
        }
        $this->proposalModel->delete($proposal['id']); 
        return redirect()->to('/peminjaman/lab-loans')->with('success', 'Proposal peminjaman berhasil dibatalkan.');
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

        if (strtotime($this->normalizeDateTime($this->request->getPost('event_end'))) <= strtotime($this->normalizeDateTime($this->request->getPost('event_start')))) {
            $this->validator->setError('event_end', 'Waktu selesai harus setelah waktu mulai.');
            return false;
        }

        return true;
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