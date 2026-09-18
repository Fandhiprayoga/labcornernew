<?php

namespace App\Controllers;

class AssetLoanReportController extends BaseController
{
    private const STATUS_OPTIONS = ['submitted', 'laboran_approved', 'rejected', 'approved', 'cancelled', 'completed'];

    public function index()
    {
        $filters = $this->filters();
        $query = $this->reportQuery($filters);
        $proposals = $query->groupBy('asset_loan_proposals.id')->orderBy('asset_loan_proposals.event_start', 'DESC')->paginate($filters['perPage']);
        $pager = $query->pager;
        $summary = $this->summary($filters);

        return $this->renderView('asset_loan_reports/index', [
            'title' => 'Laporan Peminjaman Asset',
            'page_title' => 'Laporan Peminjaman Asset',
            'proposals' => $proposals,
            'pager' => $pager,
            'totalRows' => $pager->getTotal(),
            'summary' => $summary,
            'laboratorySummary' => $this->laboratorySummary($filters),
            'topAssets' => $this->topAssets($filters),
            'overdueItems' => $this->overdueItems($filters),
            'laboratoryOptions' => $this->laboratoryOptions(),
            'statusOptions' => self::STATUS_OPTIONS,
            ...$filters,
        ]);
    }

    public function exportCsv()
    {
        $proposals = $this->reportQuery($this->filters())->groupBy('asset_loan_proposals.id')->orderBy('asset_loan_proposals.event_start', 'DESC')->findAll();
        $statusLabels = $this->statusLabels();
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Nomor Proposal', 'Pemohon', 'Nomor Identitas', 'Email', 'Kegiatan', 'Asset', 'Jumlah Asset', 'Mulai', 'Selesai', 'Durasi (Jam)', 'Status', 'Tanggal Proposal']);

        foreach ($proposals as $proposal) {
            fputcsv($stream, [
                $proposal['uuid'], $proposal['full_name'], $proposal['identity_number'], $proposal['email'],
                $proposal['event_name'], $proposal['asset_names'], $proposal['total_items'],
                $proposal['event_start'], $proposal['event_end'], $proposal['duration_hours'],
                $statusLabels[$proposal['status']] ?? $proposal['status'], $proposal['proposal_date'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $this->response
            ->download('laporan-peminjaman-asset-' . date('Y-m-d') . '.csv', $csv)
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
        return $this->proposalQuery(
            $filters,
            'asset_loan_proposals.*, GROUP_CONCAT(DISTINCT CONCAT(assets.asset_code, " - ", assets.name) ORDER BY assets.asset_code SEPARATOR ", ") AS asset_names, COUNT(DISTINCT asset_loan_proposal_items.id) AS total_items, ROUND(TIMESTAMPDIFF(MINUTE, asset_loan_proposals.event_start, asset_loan_proposals.event_end) / 60, 2) AS duration_hours'
        );
    }

    private function proposalQuery(array $filters, string $select)
    {
        $query = new \App\Models\AssetLoanProposalModel();
        $query
            ->select($select)
            ->join('asset_loan_proposal_items', 'asset_loan_proposal_items.proposal_id = asset_loan_proposals.id')
            ->join('assets', 'assets.id = asset_loan_proposal_items.asset_id')
            ->join('laboratories', 'laboratories.id = assets.laboratory_id', 'left')
            ->where('asset_loan_proposals.status !=', 'draft')
            ->where('asset_loan_proposals.event_start >=', $filters['startDate'] . ' 00:00:00')
            ->where('asset_loan_proposals.event_start <=', $filters['endDate'] . ' 23:59:59');

        $this->applyScopeAndFilters($query, $filters);
        return $query;
    }

    private function applyScopeAndFilters($query, array $filters): void
    {
        if (activeGroupIs('laboran')) {
            $query->whereIn('assets.laboratory_id', $this->assignedLaboratoryIds());
        }
        if ($filters['laboratoryUuid'] !== '') $query->where('laboratories.uuid', $filters['laboratoryUuid']);
        if ($filters['status'] !== '') $query->where('asset_loan_proposals.status', $filters['status']);
        if ($filters['q'] !== '') {
            $query->groupStart()
                ->like('asset_loan_proposals.full_name', $filters['q'])
                ->orLike('asset_loan_proposals.identity_number', $filters['q'])
                ->orLike('asset_loan_proposals.event_name', $filters['q'])
                ->orLike('assets.name', $filters['q'])
                ->groupEnd();
        }
    }

    private function summary(array $filters): array
    {
        $rows = $this->proposalQuery($filters, 'asset_loan_proposals.status, COUNT(DISTINCT asset_loan_proposals.id) AS total')->groupBy('asset_loan_proposals.status')->get()->getResultArray();
        $summary = array_fill_keys(array_merge(['total'], self::STATUS_OPTIONS), 0);
        foreach ($rows as $row) {
            $summary[$row['status']] = (int) $row['total'];
            $summary['total'] += (int) $row['total'];
        }
        return $summary;
    }

    private function laboratorySummary(array $filters): array
    {
        return $this->proposalQuery($filters, 'laboratories.name AS laboratory_name, COUNT(DISTINCT asset_loan_proposals.id) AS total_loans, COUNT(DISTINCT asset_loan_proposal_items.id) AS total_items')
            ->groupBy('laboratories.id, laboratories.name')
            ->orderBy('total_loans', 'DESC')
            ->get()->getResultArray();
    }

    private function topAssets(array $filters): array
    {
        return $this->proposalQuery($filters, 'assets.asset_code, assets.name AS asset_name, laboratories.name AS laboratory_name, COUNT(asset_loan_proposal_items.id) AS total_borrowed')
            ->groupBy('assets.id, assets.asset_code, assets.name, laboratories.name')
            ->orderBy('total_borrowed', 'DESC')
            ->limit(10)
            ->get()->getResultArray();
    }

    private function overdueItems(array $filters): array
    {
        return $this->proposalQuery(
            $filters,
            'asset_loan_proposals.uuid, asset_loan_proposals.full_name, asset_loan_proposals.event_name, asset_loan_proposals.event_end, assets.asset_code, assets.name AS asset_name, laboratories.name AS laboratory_name'
        )
            ->where('asset_loan_proposal_items.is_returned', 0)
            ->where('asset_loan_proposal_items.is_taken', 1)
            ->where('asset_loan_proposals.event_end <', date('Y-m-d H:i:s'))
            ->orderBy('asset_loan_proposals.event_end', 'ASC')
            ->get()->getResultArray();
    }

    private function laboratoryOptions(): array
    {
        $query = db_connect()->table('laboratories')
            ->select('laboratories.id, laboratories.uuid, laboratories.name')
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
