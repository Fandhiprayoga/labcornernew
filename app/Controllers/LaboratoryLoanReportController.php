<?php

namespace App\Controllers;

class LaboratoryLoanReportController extends BaseController
{
    private const STATUS_OPTIONS = ['submitted', 'laboran_approved', 'rejected', 'approved', 'cancelled', 'completed'];

    public function index()
    {
        $filters = $this->filters();
        $query = $this->reportQuery($filters);
        $proposals = $query->orderBy('laboratory_loan_proposals.event_start', 'DESC')->paginate($filters['perPage']);
        $pager = $query->pager;
        $summary = $this->summary($filters);

        return $this->renderView('loan_reports/index', [
            'title' => 'Laporan Peminjaman Laboratorium',
            'page_title' => 'Laporan Peminjaman Laboratorium',
            'proposals' => $proposals,
            'pager' => $pager,
            'totalRows' => $pager->getTotal(),
            'summary' => $summary,
            'laboratorySummary' => $this->laboratorySummary($filters),
            'laboratoryOptions' => $this->laboratoryOptions(),
            'statusOptions' => self::STATUS_OPTIONS,
            ...$filters,
        ]);
    }

    public function exportCsv()
    {
        $proposals = $this->reportQuery($this->filters())->orderBy('laboratory_loan_proposals.event_start', 'DESC')->findAll();
        $statusLabels = $this->statusLabels();
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Nomor Proposal', 'Pemohon', 'Nomor Identitas', 'Email', 'Event', 'Laboratorium', 'Ruangan', 'Mulai', 'Selesai', 'Durasi (Jam)', 'Status', 'Tanggal Proposal']);

        foreach ($proposals as $proposal) {
            fputcsv($stream, [
                $proposal['uuid'], $proposal['full_name'], $proposal['identity_number'], $proposal['email'],
                $proposal['event_name'], $proposal['laboratory_name'], trim(($proposal['room_code'] ?? '') . ' ' . ($proposal['room_name'] ?? '')),
                $proposal['event_start'], $proposal['event_end'], $proposal['duration_hours'],
                $statusLabels[$proposal['status']] ?? $proposal['status'], $proposal['proposal_date'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->download('laporan-peminjaman-laboratorium-' . date('Y-m-d') . '.csv', $csv)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    private function filters(): array
    {
        $startDate = trim((string) $this->request->getGet('start_date'));
        $endDate = trim((string) $this->request->getGet('end_date'));
        $status = trim((string) $this->request->getGet('status'));
        $perPage = (int) $this->request->getGet('perPage');

        return [
            'q' => trim((string) $this->request->getGet('q')),
            'startDate' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) ? $startDate : date('Y-m-01'),
            'endDate' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) ? $endDate : date('Y-m-d'),
            'laboratoryUuid' => trim((string) $this->request->getGet('laboratory_uuid')),
            'status' => in_array($status, self::STATUS_OPTIONS, true) ? $status : '',
            'perPage' => in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25,
        ];
    }

    private function reportQuery(array $filters)
    {
        return $this->proposalQuery($filters, 'laboratory_loan_proposals.*, laboratories.name AS laboratory_name, rooms.code AS room_code, rooms.name AS room_name, ROUND(TIMESTAMPDIFF(MINUTE, laboratory_loan_proposals.event_start, laboratory_loan_proposals.event_end) / 60, 2) AS duration_hours');
    }

    private function proposalQuery(array $filters, string $select)
    {
        $query = new \App\Models\LaboratoryLoanProposalModel();
        $query
            ->select($select)
            ->join('laboratory_loan_proposal_items', 'laboratory_loan_proposal_items.proposal_id = laboratory_loan_proposals.id')
            ->join('laboratories', 'laboratories.id = laboratory_loan_proposal_items.laboratory_id')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->where('laboratory_loan_proposals.event_start >=', $filters['startDate'] . ' 00:00:00')
            ->where('laboratory_loan_proposals.event_start <=', $filters['endDate'] . ' 23:59:59');

        $this->applyScopeAndFilters($query, $filters);
        return $query;
    }

    private function applyScopeAndFilters($query, array $filters): void
    {
        if (activeGroupIs('laboran')) {
            $query->whereIn('laboratory_loan_proposal_items.laboratory_id', $this->assignedLaboratoryIds());
        }
        if ($filters['laboratoryUuid'] !== '') $query->where('laboratories.uuid', $filters['laboratoryUuid']);
        if ($filters['status'] !== '') $query->where('laboratory_loan_proposals.status', $filters['status']);
        if ($filters['q'] !== '') {
            $query->groupStart()
                ->like('laboratory_loan_proposals.full_name', $filters['q'])
                ->orLike('laboratory_loan_proposals.identity_number', $filters['q'])
                ->orLike('laboratory_loan_proposals.event_name', $filters['q'])
                ->orLike('laboratories.name', $filters['q'])
                ->groupEnd();
        }
    }

    private function summary(array $filters): array
    {
        $rows = $this->proposalQuery($filters, 'laboratory_loan_proposals.status, COUNT(*) AS total')->groupBy('laboratory_loan_proposals.status')->get()->getResultArray();
        $summary = array_fill_keys(array_merge(['total'], self::STATUS_OPTIONS), 0);
        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['total'];
            $summary['total'] += (int) $row['total'];
        }
        return $summary;
    }

    private function laboratorySummary(array $filters): array
    {
        return $this->proposalQuery($filters, 'laboratories.name AS laboratory_name, rooms.code AS room_code, COUNT(*) AS total_loans, ROUND(SUM(TIMESTAMPDIFF(MINUTE, laboratory_loan_proposals.event_start, laboratory_loan_proposals.event_end)) / 60, 2) AS total_hours')
            ->groupBy('laboratories.id, laboratories.name, rooms.code')
            ->orderBy('total_loans', 'DESC')
            ->get()->getResultArray();
    }

    private function laboratoryOptions(): array
    {
        $query = db_connect()->table('laboratories')
            ->select('laboratories.id, laboratories.uuid, laboratories.name, rooms.code AS room_code')
            ->join('rooms', 'rooms.id = laboratories.room_id', 'left')
            ->where('laboratories.status', 'active')
            ->orderBy('laboratories.name', 'ASC');
        if (activeGroupIs('laboran')) $query->whereIn('laboratories.id', $this->assignedLaboratoryIds());
        return $query->get()->getResultArray();
    }

    private function assignedLaboratoryIds(): array
    {
        $rows = db_connect()->table('laboratory_laborans')->select('laboratory_id')->where('user_id', auth()->id())->get()->getResultArray();
        return $rows ? array_map(static fn (array $row): int => (int) $row['laboratory_id'], $rows) : [0];
    }

    private function statusLabels(): array
    {
        return ['submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'cancelled' => 'Dibatalkan', 'completed' => 'Selesai'];
    }
}