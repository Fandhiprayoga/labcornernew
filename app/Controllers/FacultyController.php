<?php

namespace App\Controllers;

use App\Models\FacultyModel;

class FacultyController extends BaseController
{
    protected FacultyModel $facultyModel;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function __construct()
    {
        $this->facultyModel = new FacultyModel();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = (string) $this->request->getGet('status');
        $perPage = (int) $this->request->getGet('perPage');

        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $query = $this->facultyModel->orderBy('code', 'ASC');

        if ($search !== '') {
            $query->groupStart()
                ->like('code', $search)
                ->orLike('name', $search)
                ->orLike('dean_name', $search)
                ->groupEnd();
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $faculties = $query->paginate($perPage);
        $pager = $this->facultyModel->pager;

        return $this->renderView('faculties/index', [
            'title'          => 'Master Fakultas',
            'page_title'     => 'Master Fakultas',
            'faculties'      => $faculties,
            'pager'          => $pager,
            'search'         => $search,
            'status'         => $status,
            'perPage'        => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage'    => $pager->getCurrentPage(),
            'totalRows'      => $pager->getTotal(),
        ]);
    }

    public function create()
    {
        return $this->renderView('faculties/create', [
            'title'      => 'Tambah Fakultas',
            'page_title' => 'Tambah Fakultas',
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->validationRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->facultyModel->insert($this->facultyData() + ['uuid' => $this->generateUuid()]);

        return redirect()->to('/admin/faculties')->with('success', 'Fakultas berhasil ditambahkan.');
    }

    public function edit(string $uuid)
    {
        $faculty = $this->findFaculty($uuid);

        if (! $faculty) {
            return redirect()->to('/admin/faculties')->with('error', 'Fakultas tidak ditemukan.');
        }

        return $this->renderView('faculties/edit', [
            'title'      => 'Edit Fakultas',
            'page_title' => 'Edit Fakultas',
            'faculty'    => $faculty,
        ]);
    }

    public function update(string $uuid)
    {
        $faculty = $this->findFaculty($uuid);

        if (! $faculty) {
            return redirect()->to('/admin/faculties')->with('error', 'Fakultas tidak ditemukan.');
        }

        if (! $this->validate($this->validationRules((int) $faculty['id']))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->facultyModel->update($faculty['id'], $this->facultyData());

        return redirect()->to('/admin/faculties')->with('success', 'Fakultas berhasil diperbarui.');
    }

    public function delete(string $uuid)
    {
        $faculty = $this->findFaculty($uuid);

        if (! $faculty) {
            return redirect()->to('/admin/faculties')->with('error', 'Fakultas tidak ditemukan.');
        }

        $this->facultyModel->delete($faculty['id']);

        return redirect()->to('/admin/faculties')->with('success', 'Fakultas berhasil dihapus.');
    }

    private function findFaculty(string $uuid): ?array
    {
        return $this->facultyModel->where('uuid', $uuid)->first();
    }

    private function validationRules(?int $id = null): array
    {
        $uniqueCode = $id === null ? 'is_unique[faculties.code]' : "is_unique[faculties.code,id,{$id}]";

        return [
            'code'        => "required|alpha_numeric_punct|min_length[2]|max_length[30]|{$uniqueCode}",
            'name'        => 'required|min_length[3]|max_length[150]',
            'dean_name'   => 'permit_empty|max_length[100]',
            'status'      => 'required|in_list[active,inactive]',
            'description' => 'permit_empty|max_length[500]',
        ];
    }

    private function facultyData(): array
    {
        return [
            'code'        => strtoupper(trim((string) $this->request->getPost('code'))),
            'name'        => trim((string) $this->request->getPost('name')),
            'dean_name'   => trim((string) $this->request->getPost('dean_name')),
            'status'      => $this->request->getPost('status'),
            'description' => trim((string) $this->request->getPost('description')),
        ];
    }

    private function generateUuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return vsprintf('%s%s%s%s-%s%s-%s%s-%s%s-%s%s%s%s%s%s', str_split(bin2hex($bytes), 2));
    }
}