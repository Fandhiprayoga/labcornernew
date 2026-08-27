<?php

namespace App\Controllers;

use App\Models\FacultyModel;
use App\Models\StudyProgramModel;

class StudyProgramController extends BaseController
{
    protected FacultyModel $facultyModel;
    protected StudyProgramModel $studyProgramModel;

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function __construct()
    {
        $this->facultyModel = new FacultyModel();
        $this->studyProgramModel = new StudyProgramModel();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $facultyId = (int) $this->request->getGet('faculty_id');
        $degree = (string) $this->request->getGet('degree');
        $status = (string) $this->request->getGet('status');
        $perPage = (int) $this->request->getGet('perPage');
        $degrees = ['D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'];

        if (! in_array($degree, $degrees, true)) {
            $degree = '';
        }

        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $query = $this->studyProgramModel
            ->select('study_programs.*, faculties.code AS faculty_code, faculties.name AS faculty_name')
            ->join('faculties', 'faculties.id = study_programs.faculty_id')
            ->orderBy('faculties.code', 'ASC')
            ->orderBy('study_programs.code', 'ASC');

        if ($search !== '') {
            $query->groupStart()
                ->like('study_programs.code', $search)
                ->orLike('study_programs.name', $search)
                ->orLike('faculties.code', $search)
                ->orLike('faculties.name', $search)
                ->groupEnd();
        }

        if ($facultyId > 0) {
            $query->where('study_programs.faculty_id', $facultyId);
        }

        if ($degree !== '') {
            $query->where('study_programs.degree', $degree);
        }

        if ($status !== '') {
            $query->where('study_programs.status', $status);
        }

        $studyPrograms = $query->paginate($perPage);
        $pager = $this->studyProgramModel->pager;

        return $this->renderView('study_programs/index', [
            'title'          => 'Manajemen Program Studi',
            'page_title'     => 'Manajemen Program Studi',
            'studyPrograms'  => $studyPrograms,
            'pager'          => $pager,
            'search'         => $search,
            'facultyId'      => $facultyId,
            'degree'         => $degree,
            'status'         => $status,
            'perPage'        => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage'    => $pager->getCurrentPage(),
            'totalRows'      => $pager->getTotal(),
            'faculties'      => $this->facultyModel->orderBy('code', 'ASC')->findAll(),
            'degrees'        => $degrees,
        ]);
    }

    public function create()
    {
        return $this->renderForm('Tambah Program Studi', 'Tambah Program Studi');
    }

    public function store()
    {
        if (! $this->validate($this->validationRules()) || ! $this->hasActiveFaculty()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->studyProgramModel->insert($this->studyProgramData() + ['uuid' => $this->generateUuid()]);

        return redirect()->to('/admin/study-programs')->with('success', 'Program studi berhasil ditambahkan.');
    }

    public function edit(string $uuid)
    {
        $studyProgram = $this->findStudyProgram($uuid);

        if (! $studyProgram) {
            return redirect()->to('/admin/study-programs')->with('error', 'Program studi tidak ditemukan.');
        }

        return $this->renderForm('Edit Program Studi', 'Edit Program Studi', $studyProgram);
    }

    public function update(string $uuid)
    {
        $studyProgram = $this->findStudyProgram($uuid);

        if (! $studyProgram) {
            return redirect()->to('/admin/study-programs')->with('error', 'Program studi tidak ditemukan.');
        }

        if (! $this->validate($this->validationRules((int) $studyProgram['id'])) || ! $this->hasActiveFaculty()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->studyProgramModel->update($studyProgram['id'], $this->studyProgramData());

        return redirect()->to('/admin/study-programs')->with('success', 'Program studi berhasil diperbarui.');
    }

    public function delete(string $uuid)
    {
        $studyProgram = $this->findStudyProgram($uuid);

        if (! $studyProgram) {
            return redirect()->to('/admin/study-programs')->with('error', 'Program studi tidak ditemukan.');
        }

        $this->studyProgramModel->delete($studyProgram['id']);

        return redirect()->to('/admin/study-programs')->with('success', 'Program studi berhasil dihapus.');
    }

    private function renderForm(string $title, string $pageTitle, ?array $studyProgram = null)
    {
        return $this->renderView($studyProgram ? 'study_programs/edit' : 'study_programs/create', [
            'title' => $title,
            'page_title' => $pageTitle,
            'faculties' => $this->facultyModel->where('status', 'active')->orderBy('name', 'ASC')->findAll(),
            'studyProgram' => $studyProgram,
        ]);
    }

    private function findStudyProgram(string $uuid): ?array
    {
        return $this->studyProgramModel->where('uuid', $uuid)->first();
    }

    private function validationRules(?int $id = null): array
    {
        $uniqueCode = $id === null ? 'is_unique[study_programs.code]' : "is_unique[study_programs.code,id,{$id}]";

        return [
            'faculty_id' => 'required|integer|is_not_unique[faculties.id]',
            'code' => "required|alpha_numeric_punct|min_length[2]|max_length[30]|{$uniqueCode}",
            'name' => 'required|min_length[3]|max_length[150]',
            'degree' => 'required|in_list[D1,D2,D3,D4,S1,S2,S3,Profesi]',
            'status' => 'required|in_list[active,inactive]',
            'description' => 'permit_empty|max_length[500]',
        ];
    }

    private function hasActiveFaculty(): bool
    {
        $facultyId = (int) $this->request->getPost('faculty_id');

        if ($facultyId > 0 && $this->facultyModel->where('status', 'active')->find($facultyId)) {
            return true;
        }

        $this->validator->setError('faculty_id', 'Fakultas yang dipilih tidak tersedia atau tidak aktif.');

        return false;
    }

    private function studyProgramData(): array
    {
        return [
            'faculty_id' => (int) $this->request->getPost('faculty_id'),
            'code' => strtoupper(trim((string) $this->request->getPost('code'))),
            'name' => trim((string) $this->request->getPost('name')),
            'degree' => $this->request->getPost('degree'),
            'status' => $this->request->getPost('status'),
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