<?php

namespace App\Controllers;

use App\Models\LaboratoryLaboranModel;
use App\Models\LaboratoryModel;

class LaboratoryLaboranController extends BaseController
{
    protected LaboratoryLaboranModel $laboratoryLaboranModel;
    protected LaboratoryModel $laboratoryModel;

    public function __construct()
    {
        $this->laboratoryLaboranModel = new LaboratoryLaboranModel();
        $this->laboratoryModel = new LaboratoryModel();
    }

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index()
    {
        $search       = trim((string) $this->request->getGet('q'));
        $laboratoryId = (int) $this->request->getGet('laboratory_id');
        $perPage      = (int) $this->request->getGet('perPage');

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $query = $this->laboratoryLaboranModel
            ->select('laboratory_laborans.id, users.username, auth_identities.secret AS email, laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name')
            ->join('users', 'users.id = laboratory_laborans.user_id')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->join('laboratories', 'laboratories.id = laboratory_laborans.laboratory_id')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left');

        if ($search !== '') {
            $query->groupStart()
                ->like('users.username', $search)
                ->orLike('auth_identities.secret', $search)
                ->orLike('laboratories.name', $search)
                ->orLike('rooms.code', $search)
                ->orLike('rooms.name', $search)
                ->groupEnd();
        }

        if ($laboratoryId > 0) {
            $query->where('laboratory_laborans.laboratory_id', $laboratoryId);
        }

        $assignments = $query
            ->orderBy('users.username', 'ASC')
            ->orderBy('laboratories.name', 'ASC')
            ->paginate($perPage);

        $pager = $this->laboratoryLaboranModel->pager;

        return $this->renderView('laboratory_laborans/index', [
            'title' => 'Master Laboran',
            'page_title' => 'Master Laboran',
            'assignments' => $assignments,
            'pager' => $pager,
            'search' => $search,
            'laboratoryId' => $laboratoryId,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $pager->getCurrentPage(),
            'totalRows' => $pager->getTotal(),
            'laboratoryOptions' => $this->laboratoryModel
                ->select('laboratories.id, laboratories.name, rooms.code AS room_code')
                ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
                ->orderBy('laboratories.name', 'ASC')
                ->findAll(),
        ]);
    }

    public function create()
    {
        return $this->renderForm('Tambah Penugasan Laboran', 'Tambah Penugasan Laboran');
    }

    public function store()
    {
        if (! $this->isValidSubmission()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->laboratoryLaboranModel->insert($this->assignmentData());

        return redirect()->to('/admin/laboratory-laborans')->with('success', 'Penugasan laboran berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $assignment = $this->laboratoryLaboranModel->find($id);

        if (! $assignment) {
            return redirect()->to('/admin/laboratory-laborans')->with('error', 'Penugasan laboran tidak ditemukan.');
        }

        return $this->renderForm('Edit Penugasan Laboran', 'Edit Penugasan Laboran', $assignment);
    }

    public function update(int $id)
    {
        if (! $this->laboratoryLaboranModel->find($id)) {
            return redirect()->to('/admin/laboratory-laborans')->with('error', 'Penugasan laboran tidak ditemukan.');
        }

        if (! $this->isValidSubmission($id)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->laboratoryLaboranModel->update($id, $this->assignmentData());

        return redirect()->to('/admin/laboratory-laborans')->with('success', 'Penugasan laboran berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (! $this->laboratoryLaboranModel->find($id)) {
            return redirect()->to('/admin/laboratory-laborans')->with('error', 'Penugasan laboran tidak ditemukan.');
        }

        $this->laboratoryLaboranModel->delete($id);

        return redirect()->to('/admin/laboratory-laborans')->with('success', 'Penugasan laboran berhasil dihapus.');
    }

    private function renderForm(string $title, string $pageTitle, ?array $assignment = null)
    {
        return $this->renderView($assignment ? 'laboratory_laborans/edit' : 'laboratory_laborans/create', [
            'title' => $title,
            'page_title' => $pageTitle,
            'assignment' => $assignment,
            'laborans' => $this->laboranOptions(),
            'laboratories' => $this->laboratoryModel
                ->select('laboratories.id, laboratories.name, rooms.code AS room_code, rooms.name AS room_name')
                ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
                ->where('laboratories.status', 'active')
                ->orderBy('laboratories.name', 'ASC')
                ->findAll(),
        ]);
    }

    private function isValidSubmission(?int $assignmentId = null): bool
    {
        if (! $this->validate([
            'user_id' => 'required|integer|is_not_unique[users.id]',
            'laboratory_id' => 'required|integer|is_not_unique[laboratories.id]',
        ])) {
            return false;
        }

        $userId = (int) $this->request->getPost('user_id');
        $laboratoryId = (int) $this->request->getPost('laboratory_id');

        if (! $this->isLaboran($userId)) {
            $this->validator->setError('user_id', 'User yang dipilih harus memiliki role Laboran.');

            return false;
        }

        $laboratory = $this->laboratoryModel->where('status', 'active')->find($laboratoryId);

        if (! $laboratory) {
            $this->validator->setError('laboratory_id', 'Laboratorium yang dipilih tidak tersedia atau tidak aktif.');

            return false;
        }

        $duplicate = $this->laboratoryLaboranModel
            ->where('user_id', $userId)
            ->where('laboratory_id', $laboratoryId);

        if ($assignmentId !== null) {
            $duplicate->where('id !=', $assignmentId);
        }

        if ($duplicate->first()) {
            $this->validator->setError('laboratory_id', 'Laboran tersebut sudah ditugaskan di laboratorium ini.');

            return false;
        }

        return true;
    }

    private function assignmentData(): array
    {
        return [
            'user_id' => (int) $this->request->getPost('user_id'),
            'laboratory_id' => (int) $this->request->getPost('laboratory_id'),
        ];
    }

    private function laboranOptions(): array
    {
        return db_connect()->table('users')
            ->select('users.id, users.username, auth_identities.secret AS email')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->join('auth_identities', "auth_identities.user_id = users.id AND auth_identities.type = 'email_password'", 'left')
            ->where('auth_groups_users.group', 'laboran')
            ->where('users.deleted_at', null)
            ->orderBy('users.username', 'ASC')
            ->get()
            ->getResultArray();
    }

    private function isLaboran(int $userId): bool
    {
        return db_connect()->table('auth_groups_users')
            ->where('user_id', $userId)
            ->where('group', 'laboran')
            ->countAllResults() > 0;
    }
}