<?php

namespace App\Controllers;

use App\Models\LaboratoryModel;
use App\Models\LaboratoryStudyProgramModel;
use App\Models\RoomModel;
use App\Models\StudyProgramModel;

class LaboratoryController extends BaseController
{
    protected LaboratoryModel $laboratoryModel;
    protected LaboratoryStudyProgramModel $laboratoryStudyProgramModel;
    protected RoomModel $roomModel;
    protected StudyProgramModel $studyProgramModel;

    public function __construct()
    {
        $this->laboratoryModel = new LaboratoryModel();
        $this->laboratoryStudyProgramModel = new LaboratoryStudyProgramModel();
        $this->roomModel = new RoomModel();
        $this->studyProgramModel = new StudyProgramModel();
    }

    public function index()
    {
        return $this->renderView('laboratories/index', [
            'title' => 'Master Laboratorium',
            'page_title' => 'Master Laboratorium',
            'laboratories' => $this->laboratoryModel
                ->select("laboratories.*, rooms.code AS room_code, rooms.name AS room_name, GROUP_CONCAT(CONCAT(study_programs.degree, ' ', study_programs.code) ORDER BY study_programs.degree, study_programs.code SEPARATOR ', ') AS study_programs")
                ->join('rooms', 'rooms.id = laboratories.room_id')
                ->join('laboratory_study_programs', 'laboratory_study_programs.laboratory_id = laboratories.id', 'left')
                ->join('study_programs', 'study_programs.id = laboratory_study_programs.study_program_id AND study_programs.deleted_at IS NULL', 'left')
                ->groupBy('laboratories.id')
                ->orderBy('laboratories.name', 'ASC')
                ->findAll(),
        ]);
    }

    public function create()
    {
        return $this->renderForm('Tambah Laboratorium', 'Tambah Laboratorium');
    }

    public function store()
    {
        if (! $this->isValidSubmission()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $database = db_connect();
        $database->transStart();
        $this->laboratoryModel->insert($this->laboratoryData());
        $this->syncStudyPrograms((int) $this->laboratoryModel->getInsertID());
        $database->transComplete();

        if (! $database->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Laboratorium gagal ditambahkan.');
        }

        return redirect()->to('/admin/laboratories')->with('success', 'Laboratorium berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $laboratory = $this->laboratoryModel->find($id);

        if (! $laboratory) {
            return redirect()->to('/admin/laboratories')->with('error', 'Laboratorium tidak ditemukan.');
        }

        $laboratory['study_program_ids'] = array_column(
            $this->laboratoryStudyProgramModel->where('laboratory_id', $id)->findAll(),
            'study_program_id'
        );

        return $this->renderForm('Edit Laboratorium', 'Edit Laboratorium', $laboratory);
    }

    public function update(int $id)
    {
        if (! $this->laboratoryModel->find($id)) {
            return redirect()->to('/admin/laboratories')->with('error', 'Laboratorium tidak ditemukan.');
        }

        if (! $this->isValidSubmission()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $database = db_connect();
        $database->transStart();
        $this->laboratoryModel->update($id, $this->laboratoryData());
        $this->syncStudyPrograms($id);
        $database->transComplete();

        if (! $database->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Laboratorium gagal diperbarui.');
        }

        return redirect()->to('/admin/laboratories')->with('success', 'Laboratorium berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        if (! $this->laboratoryModel->find($id)) {
            return redirect()->to('/admin/laboratories')->with('error', 'Laboratorium tidak ditemukan.');
        }

        $this->laboratoryModel->delete($id);

        return redirect()->to('/admin/laboratories')->with('success', 'Laboratorium berhasil dihapus.');
    }

    private function renderForm(string $title, string $pageTitle, ?array $laboratory = null)
    {
        return $this->renderView($laboratory ? 'laboratories/edit' : 'laboratories/create', [
            'title' => $title,
            'page_title' => $pageTitle,
            'laboratory' => $laboratory,
            'rooms' => $this->roomModel->where('type', 'laboratorium')->where('status', 'active')->orderBy('code', 'ASC')->findAll(),
            'studyPrograms' => $this->studyProgramModel->where('status', 'active')->orderBy('degree', 'ASC')->orderBy('code', 'ASC')->findAll(),
        ]);
    }

    private function isValidSubmission(): bool
    {
        if (! $this->validate([
            'room_id' => 'required|integer|is_not_unique[rooms.id]',
            'name' => 'required|min_length[3]|max_length[150]',
            'study_program_ids' => 'required',
            'status' => 'required|in_list[active,inactive]',
            'description' => 'permit_empty|max_length[500]',
        ])) {
            return false;
        }

        $room = $this->roomModel
            ->where('type', 'laboratorium')
            ->where('status', 'active')
            ->find((int) $this->request->getPost('room_id'));

        if (! $room) {
            $this->validator->setError('room_id', 'Ruangan laboratorium yang dipilih tidak tersedia atau tidak aktif.');

            return false;
        }

        $studyProgramIds = $this->studyProgramIds();
        $activeStudyProgramCount = $this->studyProgramModel
            ->where('status', 'active')
            ->whereIn('id', $studyProgramIds)
            ->countAllResults();

        if ($activeStudyProgramCount !== count($studyProgramIds)) {
            $this->validator->setError('study_program_ids', 'Semua program studi yang dipilih harus aktif dan tersedia.');

            return false;
        }

        return true;
    }

    private function laboratoryData(): array
    {
        return [
            'room_id' => (int) $this->request->getPost('room_id'),
            'name' => trim((string) $this->request->getPost('name')),
            'status' => $this->request->getPost('status'),
            'description' => trim((string) $this->request->getPost('description')),
        ];
    }

    private function studyProgramIds(): array
    {
        return array_values(array_unique(array_map('intval', (array) $this->request->getPost('study_program_ids'))));
    }

    private function syncStudyPrograms(int $laboratoryId): void
    {
        $this->laboratoryStudyProgramModel->where('laboratory_id', $laboratoryId)->delete();

        foreach ($this->studyProgramIds() as $studyProgramId) {
            $this->laboratoryStudyProgramModel->insert([
                'laboratory_id' => $laboratoryId,
                'study_program_id' => $studyProgramId,
            ]);
        }
    }
}