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

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function __construct()
    {
        $this->laboratoryModel = new LaboratoryModel();
        $this->laboratoryStudyProgramModel = new LaboratoryStudyProgramModel();
        $this->roomModel = new RoomModel();
        $this->studyProgramModel = new StudyProgramModel();
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $roomId = (int) $this->request->getGet('room_id');
        $studyProgramId = (int) $this->request->getGet('study_program_id');
        $status = (string) $this->request->getGet('status');
        $perPage = (int) $this->request->getGet('perPage');

        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::PER_PAGE_OPTIONS[0];
        }

        $query = $this->laboratoryModel
              ->select("laboratories.*, rooms.code AS room_code, rooms.name AS room_name, GROUP_CONCAT(DISTINCT study_programs.name ORDER BY study_programs.name SEPARATOR ', ') AS study_programs")
            ->join('rooms', 'rooms.id = laboratories.room_id')
            ->join('laboratory_study_programs', 'laboratory_study_programs.laboratory_id = laboratories.id', 'left')
            ->join('study_programs', 'study_programs.id = laboratory_study_programs.study_program_id AND study_programs.deleted_at IS NULL', 'left');

        if ($search !== '') {
            $query->groupStart()
                ->like('laboratories.name', $search)
                ->orLike('laboratories.description', $search)
                ->orLike('rooms.code', $search)
                ->orLike('rooms.name', $search)
                ->orLike('study_programs.code', $search)
                ->orLike('study_programs.name', $search)
                ->orLike('study_programs.degree', $search)
                ->groupEnd();
        }

        if ($roomId > 0) {
            $query->where('laboratories.room_id', $roomId);
        }

        if ($studyProgramId > 0) {
            $query->join('laboratory_study_programs selected_study_programs', 'selected_study_programs.laboratory_id = laboratories.id')
                ->where('selected_study_programs.study_program_id', $studyProgramId);
        }

        if ($status !== '') {
            $query->where('laboratories.status', $status);
        }

        $laboratories = $query
            ->groupBy('laboratories.id')
            ->orderBy('laboratories.name', 'ASC')
            ->paginate($perPage);
        $pager = $this->laboratoryModel->pager;

        return $this->renderView('laboratories/index', [
            'title' => 'Master Laboratorium',
            'page_title' => 'Master Laboratorium',
            'laboratories' => $laboratories,
            'pager' => $pager,
            'search' => $search,
            'roomId' => $roomId,
            'studyProgramId' => $studyProgramId,
            'status' => $status,
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE_OPTIONS,
            'currentPage' => $pager->getCurrentPage(),
            'totalRows' => $pager->getTotal(),
            'rooms' => $this->roomModel->where('type', 'laboratorium')->orderBy('code', 'ASC')->findAll(),
            'studyPrograms' => $this->studyProgramModel->orderBy('degree', 'ASC')->orderBy('code', 'ASC')->findAll(),
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

    public function edit(string $uuid)
    {
        $laboratory = $this->laboratoryModel->findByUuid($uuid);

        if (! $laboratory) {
            return redirect()->to('/admin/laboratories')->with('error', 'Laboratorium tidak ditemukan.');
        }

        $laboratory['study_program_ids'] = array_column(
            $this->laboratoryStudyProgramModel->where('laboratory_id', $laboratory['id'])->findAll(),
            'study_program_id'
        );

        return $this->renderForm('Edit Laboratorium', 'Edit Laboratorium', $laboratory);
    }

    public function update(string $uuid)
    {
        $laboratory = $this->laboratoryModel->findByUuid($uuid);

        if (! $laboratory) {
            return redirect()->to('/admin/laboratories')->with('error', 'Laboratorium tidak ditemukan.');
        }

        if (! $this->isValidSubmission()) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $laboratoryData = $this->laboratoryData($laboratory['photo'] ?? null);

        $database = db_connect();
        $database->transStart();
        $this->laboratoryModel->update($laboratory['id'], $laboratoryData);
        $this->syncStudyPrograms($laboratory['id']);
        $database->transComplete();

        if (! $database->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Laboratorium gagal diperbarui.');
        }

        if (($laboratory['photo'] ?? null) !== ($laboratoryData['photo'] ?? null)) {
            $this->deletePhoto($laboratory['photo'] ?? null);
        }

        return redirect()->to('/admin/laboratories')->with('success', 'Laboratorium berhasil diperbarui.');
    }

    public function delete(string $uuid)
    {
        $laboratory = $this->laboratoryModel->findByUuid($uuid);

        if (! $laboratory) {
            return redirect()->to('/admin/laboratories')->with('error', 'Laboratorium tidak ditemukan.');
        }

        $this->laboratoryModel->delete($laboratory['id']);
        $this->deletePhoto($laboratory['photo'] ?? null);

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

        $photo = $this->request->getFile('photo');
        if ($photo && $photo->getError() !== UPLOAD_ERR_NO_FILE && ! $this->validate([
            'photo' => 'uploaded[photo]|max_size[photo,2048]|is_image[photo]|mime_in[photo,image/jpg,image/jpeg,image/png,image/webp]',
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

    private function laboratoryData(?string $currentPhoto = null): array
    {
        return [
            'room_id' => (int) $this->request->getPost('room_id'),
            'name' => trim((string) $this->request->getPost('name')),
            'photo' => $this->storePhoto($currentPhoto),
            'status' => $this->request->getPost('status'),
            'description' => trim((string) $this->request->getPost('description')),
        ];
    }

    private function storePhoto(?string $currentPhoto = null): ?string
    {
        $photo = $this->request->getFile('photo');

        if (! $photo || $photo->getError() === UPLOAD_ERR_NO_FILE) {
            return $currentPhoto;
        }

        $uploadPath = FCPATH . 'uploads/laboratories';
        if (! is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = bin2hex(random_bytes(16)) . '.' . $photo->getExtension();
        $photo->move($uploadPath, $fileName);

        return 'uploads/laboratories/' . $fileName;
    }

    private function deletePhoto(?string $photo): void
    {
        if ($photo && str_starts_with($photo, 'uploads/laboratories/')) {
            $filePath = FCPATH . $photo;
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
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