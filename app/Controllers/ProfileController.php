<?php

namespace App\Controllers;

use App\Models\StudyProgramModel;

class ProfileController extends BaseController
{
    protected StudyProgramModel $studyProgramModel;

    public function __construct()
    {
        $this->studyProgramModel = new StudyProgramModel();
    }

    public function index()
    {
        $user = auth()->user();

        $currentStudyProgram = $user->study_program_id
            ? $this->studyProgramModel->find($user->study_program_id)
            : null;

        $data = [
            'title'               => 'Profil Saya',
            'page_title'          => 'Profil',
            'user'                => $user,
            'userGroups'          => $user->getGroups(),
            'studyPrograms'       => $this->studyProgramModel->where('status', 'active')->orderBy('code', 'ASC')->findAll(),
            'currentStudyProgram' => $currentStudyProgram,
        ];

        return $this->renderView('profile/index', $data);
    }

    public function update()
    {
        $user = auth()->user();

        $rules = [
            'username'         => 'required|min_length[3]|max_length[30]',
            'phone'            => 'permit_empty|max_length[20]|regex_match[/^[0-9+\-\s()]+$/]',
            'study_program_id' => 'permit_empty|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $user->username         = $this->request->getPost('username');
        $user->phone            = $this->request->getPost('phone') ?: null;
        $user->study_program_id = $this->request->getPost('study_program_id') ?: null;

        // Update password jika diisi
        $password = $this->request->getPost('password');
        if (! empty($password)) {
            $user->password = $password;
        }

        $users = auth()->getProvider();
        $users->save($user);

        return redirect()->to('/profile')->with('success', 'Profil berhasil diperbarui.');
    }
}
