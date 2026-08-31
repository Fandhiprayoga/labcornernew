<?php

namespace App\Controllers;

use App\Models\StudyProgramModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $user = auth()->user();

        $studyProgramModel = new StudyProgramModel();
        $currentStudyProgram = $user->study_program_id
            ? $studyProgramModel->find($user->study_program_id)
            : null;

        $data = [
            'title'               => 'Dashboard',
            'page_title'          => 'Dashboard',
            'user'                => $user,
            'userGroups'          => $user->getGroups(),
            'currentStudyProgram' => $currentStudyProgram,
        ];

        return $this->renderView('dashboard/index', $data);
    }
}
