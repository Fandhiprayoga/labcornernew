<?php

namespace App\Controllers;

use App\Models\LaboratoryLoanProposalItemModel;
use App\Models\LaboratoryLoanProposalModel;
use App\Models\LaboratoryLaboranModel;
use App\Models\LaboratoryModel;
use App\Models\StudyProgramModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $user = auth()->user();
        $userGroups = $user->getGroups();

        $studyProgramModel = new StudyProgramModel();
        $currentStudyProgram = $user->study_program_id
            ? $studyProgramModel->find($user->study_program_id)
            : null;

        $laboratoryModel = new LaboratoryModel();
        $laboratories = $laboratoryModel->orderBy('name', 'ASC')->findAll();

        $selectedLaboratoryUuid = (string) (service('request')->getGet('laboratory_uuid') ?? '');

        $loanProposalModel = new LaboratoryLoanProposalModel();
        $loanEvents = $loanProposalModel
            ->select('id, uuid, event_name, event_start, event_end, status, full_name')
            ->whereIn('status', ['approved', 'completed'])
            ->orderBy('event_start', 'ASC')
            ->findAll();

        if ($loanEvents !== []) {
            $itemModel = new LaboratoryLoanProposalItemModel();
            $laboratoryLinks = $itemModel
                ->select('laboratory_loan_proposal_items.proposal_id, laboratories.uuid AS laboratory_uuid, laboratories.name AS laboratory_name')
                ->join('laboratories', 'laboratories.id = laboratory_loan_proposal_items.laboratory_id')
                ->whereIn('laboratory_loan_proposal_items.proposal_id', array_column($loanEvents, 'id'))
                ->orderBy('laboratories.name', 'ASC')
                ->findAll();

            $laboratoryNamesByProposal = [];
            $laboratoryUuidsByProposal = [];
            foreach ($laboratoryLinks as $row) {
                $laboratoryNamesByProposal[$row['proposal_id']][] = $row['laboratory_name'];
                $laboratoryUuidsByProposal[$row['proposal_id']][] = $row['laboratory_uuid'];
            }

            foreach ($loanEvents as &$loanEvent) {
                $loanEvent['laboratories'] = implode(', ', $laboratoryNamesByProposal[$loanEvent['id']] ?? []);
            }
            unset($loanEvent);

            if ($selectedLaboratoryUuid !== '') {
                $loanEvents = array_values(array_filter(
                    $loanEvents,
                    static fn (array $loanEvent): bool => in_array($selectedLaboratoryUuid, $laboratoryUuidsByProposal[$loanEvent['id']] ?? [], true)
                ));
            }
        }

        $laboranAssignments = [];
        if (in_array('laboran', $userGroups, true)) {
            $laboranAssignments = (new LaboratoryLaboranModel())
                ->select('laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name')
                ->join('laboratories', 'laboratories.id = laboratory_laborans.laboratory_id')
                ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
                ->where('laboratory_laborans.user_id', $user->id)
                ->orderBy('laboratories.name', 'ASC')
                ->findAll();
        }

        $data = [
            'title'                  => 'Dashboard',
            'page_title'             => 'Dashboard',
            'user'                   => $user,
            'userGroups'             => $userGroups,
            'currentStudyProgram'    => $currentStudyProgram,
            'laboranAssignments'     => $laboranAssignments,
            'loanEvents'             => $loanEvents,
            'laboratories'           => $laboratories,
            'selectedLaboratoryUuid' => $selectedLaboratoryUuid,
        ];


        return $this->renderView('dashboard/index', $data);
    }
}

