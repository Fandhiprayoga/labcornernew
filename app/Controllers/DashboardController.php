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
                ->select('laboratory_laborans.laboratory_id, laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name')
                ->join('laboratories', 'laboratories.id = laboratory_laborans.laboratory_id')
                ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
                ->where('laboratory_laborans.user_id', $user->id)
                ->orderBy('laboratories.name', 'ASC')
                ->findAll();
        }

        $activeGroup = activeGroup();
        $overview = null;
        if ($activeGroup === 'laboran') {
            $overview = $this->laboratoryOverview(array_map('intval', array_column($laboranAssignments, 'laboratory_id')));
        } elseif (in_array($activeGroup, ['superadmin', 'kepala_lab'], true)) {
            $overview = $this->laboratoryOverview();
        } elseif ($activeGroup === 'user') {
            $overview = $this->userLoanOverview((int) $user->id);
        }

        $data = [
            'title'                  => 'Dashboard',
            'page_title'             => 'Dashboard',
            'user'                   => $user,
            'userGroups'             => $userGroups,
            'currentStudyProgram'    => $currentStudyProgram,
            'laboranAssignments'     => $laboranAssignments,
            'overview'               => $overview,
            'loanEvents'             => $loanEvents,
            'laboratories'           => $laboratories,
            'selectedLaboratoryUuid' => $selectedLaboratoryUuid,
        ];


        return $this->renderView('dashboard/index', $data);
    }

    private function laboratoryOverview(?array $laboratoryIds = null): array
    {
        if ($laboratoryIds === []) {
            return ['laboratories' => 0, 'assets' => 0, 'laboratoryLoans' => 0, 'assetLoans' => 0];
        }

        $db = db_connect();
        $activeStatuses = ['submitted', 'laboran_approved', 'approved'];

        $laboratoryQuery = $db->table('laboratories')
            ->where('status', 'active')
            ->where('deleted_at', null);
        $assetQuery = $db->table('assets')->where('deleted_at', null);
        $laboratoryLoanQuery = $db->table('laboratory_loan_proposal_items AS items')
            ->select('items.proposal_id')
            ->join('laboratory_loan_proposals AS proposals', 'proposals.id = items.proposal_id')
            ->where('items.deleted_at', null)
            ->where('proposals.deleted_at', null)
            ->whereIn('proposals.status', $activeStatuses);
        $assetLoanQuery = $db->table('asset_loan_proposal_items AS items')
            ->select('items.proposal_id')
            ->join('asset_loan_proposals AS proposals', 'proposals.id = items.proposal_id')
            ->join('assets', 'assets.id = items.asset_id')
            ->where('items.deleted_at', null)
            ->where('proposals.deleted_at', null)
            ->where('assets.deleted_at', null)
            ->whereIn('proposals.status', $activeStatuses);

        if ($laboratoryIds !== null) {
            $laboratoryQuery->whereIn('id', $laboratoryIds);
            $assetQuery->whereIn('laboratory_id', $laboratoryIds);
            $laboratoryLoanQuery->whereIn('items.laboratory_id', $laboratoryIds);
            $assetLoanQuery->whereIn('assets.laboratory_id', $laboratoryIds);
        }

        return [
            'laboratories' => $laboratoryQuery->countAllResults(),
            'assets' => $assetQuery->countAllResults(),
            'laboratoryLoans' => count($laboratoryLoanQuery->groupBy('items.proposal_id')->get()->getResultArray()),
            'assetLoans' => count($assetLoanQuery->groupBy('items.proposal_id')->get()->getResultArray()),
        ];
    }

    private function userLoanOverview(int $userId): array
    {
        $db = db_connect();
        $pendingStatuses = ['submitted', 'laboran_approved'];
        $activeStatuses = ['approved'];

        $countProposals = static function (string $table, array $statuses) use ($db, $userId): int {
            return $db->table($table)
                ->where('user_id', $userId)
                ->where('deleted_at', null)
                ->whereIn('status', $statuses)
                ->countAllResults();
        };

        return [
            'laboratoryPending' => $countProposals('laboratory_loan_proposals', $pendingStatuses),
            'laboratoryActive' => $countProposals('laboratory_loan_proposals', $activeStatuses),
            'assetPending' => $countProposals('asset_loan_proposals', $pendingStatuses),
            'assetActive' => $countProposals('asset_loan_proposals', $activeStatuses),
        ];
    }
}

