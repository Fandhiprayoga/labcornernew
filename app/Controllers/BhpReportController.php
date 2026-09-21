<?php

namespace App\Controllers;

use App\Models\BhpRequestModel;

class BhpReportController extends BaseController
{
    public function index()
    {
        $filters = $this->filters();
        $rows = $this->reportQuery($filters)
            ->select('study_programs.code AS program_code, study_programs.name AS program_name, COUNT(DISTINCT pengajuan_bhp.id) AS total_pengajuan, SUM(pengajuan_bhp.grand_total_estimasi) AS total_estimasi, SUM(COALESCE(pengajuan_bhp.realisasi_biaya, 0)) AS total_realisasi, GROUP_CONCAT(DISTINCT laboratories.name ORDER BY laboratories.name SEPARATOR ", ") AS laboratories')
            ->groupBy('pengajuan_bhp.study_program_id, study_programs.code, study_programs.name')
            ->orderBy('study_programs.name', 'ASC')
            ->findAll();

        return $this->renderView('bhp/report', [
            'title' => 'Laporan Realisasi BHP',
            'page_title' => 'Laporan BHP per Program Studi',
            'rows' => $rows,
            ...$filters,
        ]);
    }

    public function exportCsv()
    {
        $filters = $this->filters();
        $rows = $this->reportQuery($filters)
            ->select('study_programs.code AS program_code, study_programs.name AS program_name, COUNT(DISTINCT pengajuan_bhp.id) AS total_pengajuan, SUM(pengajuan_bhp.grand_total_estimasi) AS total_estimasi, SUM(COALESCE(pengajuan_bhp.realisasi_biaya, 0)) AS total_realisasi, GROUP_CONCAT(DISTINCT laboratories.name ORDER BY laboratories.name SEPARATOR ", ") AS laboratories')
            ->groupBy('pengajuan_bhp.study_program_id, study_programs.code, study_programs.name')
            ->orderBy('study_programs.name', 'ASC')
            ->findAll();
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['Kode Prodi', 'Program Studi', 'Laboratorium', 'Jumlah Pengajuan', 'Total Estimasi', 'Total Realisasi', 'Selisih']);
        foreach ($rows as $row) {
            fputcsv($stream, [$row['program_code'] ?? '-', $row['program_name'] ?? 'Tanpa Prodi', $row['laboratories'] ?? '-', $row['total_pengajuan'], $row['total_estimasi'], $row['total_realisasi'], (float) $row['total_realisasi'] - (float) $row['total_estimasi']]);
        }
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        return $this->response->download('laporan-bhp-per-program-studi-' . date('Y-m-d') . '.csv', $csv)->setHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    private function reportQuery(array $filters)
    {
        $query = (new BhpRequestModel())
            ->join('laboratories', 'laboratories.id = pengajuan_bhp.laboratory_id')
            ->join('study_programs', 'study_programs.id = pengajuan_bhp.study_program_id', 'left')
            ->whereNotIn('pengajuan_bhp.status', ['DRAFT', 'REJECTED']);
        if ($filters['start_date'] !== '') $query->where('pengajuan_bhp.created_at >=', $filters['start_date'] . ' 00:00:00');
        if ($filters['end_date'] !== '') $query->where('pengajuan_bhp.created_at <=', $filters['end_date'] . ' 23:59:59');
        if ($filters['status'] !== '') $query->where('pengajuan_bhp.status', $filters['status']);
        if ($filters['q'] !== '') $query->groupStart()->like('study_programs.code', $filters['q'])->orLike('study_programs.name', $filters['q'])->orLike('laboratories.name', $filters['q'])->groupEnd();
        return $query;
    }

    private function filters(): array
    {
        $start = trim((string) $this->request->getGet('start_date'));
        $end = trim((string) $this->request->getGet('end_date'));
        $status = trim((string) $this->request->getGet('status'));
        return [
            'q' => trim((string) $this->request->getGet('q')),
            'start_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) ? $start : date('Y-m-01'),
            'end_date' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) ? $end : date('Y-m-d'),
            'status' => in_array($status, ['PENDING_REVIEW', 'NEED_REVISION', 'APPROVED_BY_KALAB', 'FUND_DISBURSED', 'EVIDEN_SUBMITTED', 'COMPLETED'], true) ? $status : '',
        ];
    }
}
