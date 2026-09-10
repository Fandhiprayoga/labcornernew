<?php

namespace App\Controllers;

use App\Models\LaboratoryLoanProposalModel;
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

        $loanProposalModel = new LaboratoryLoanProposalModel();
        $loanQuery = $loanProposalModel
            ->select('id, uuid, event_name, event_start, event_end, status, full_name')
            ->whereIn('status', ['approved', 'completed']);

        if (! activeGroupIs('superadmin', 'kepala_lab', 'laboran')) {
            $loanQuery->where('user_id', auth()->id());
        }

        $loanEvents = $loanQuery->orderBy('event_start', 'ASC')->findAll();

        $data = [
            'title'               => 'Dashboard',
            'page_title'          => 'Dashboard',
            'user'                => $user,
            'userGroups'          => $user->getGroups(),
            'currentStudyProgram' => $currentStudyProgram,
            'loanEvents'          => $loanEvents,
        ];

        return $this->renderView('dashboard/index', $data);
    }
}
