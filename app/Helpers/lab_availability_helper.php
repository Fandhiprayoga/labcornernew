<?php

/**
 * Helper ketersediaan ruangan laboratorium.
 *
 * Menyediakan query terpusat untuk mendeteksi bentrok (overlap) jadwal pemakaian
 * laboratorium, sehingga dapat dipakai ulang oleh modul peminjaman, penjadwalan,
 * maupun laporan tanpa menduplikasi query.
 *
 * Aturan overlap yang dipakai (half-open interval):
 *   existing.event_start < requested_end AND existing.event_end > requested_start
 * Sehingga jadwal yang bersambung persis (10:00-12:00 dan 12:00-14:00) tidak dianggap bentrok.
 */

if (! function_exists('lab_availability_blocking_statuses')) {
    /**
     * Status proposal yang mengunci (membooking) ruangan laboratorium.
     *
     * @return list<string>
     */
    function lab_availability_blocking_statuses(): array
    {
        return ['submitted', 'approved'];
    }
}

if (! function_exists('lab_availability_normalize_datetime')) {
    /**
     * Normalisasi input datetime (mendukung format `Y-m-d\TH:i` dari input HTML).
     */
    function lab_availability_normalize_datetime(string $value): string
    {
        $value = str_replace('T', ' ', trim($value));

        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date('Y-m-d H:i:s', $timestamp);
    }
}

if (! function_exists('lab_availability_conflicts')) {
    /**
     * Ambil seluruh booking yang bentrok pada rentang waktu tertentu.
     *
     * @param list<int>|int|null $laboratoryIds Batasi ke laboratorium tertentu, null = semua.
     * @param int|null           $excludeProposalId Abaikan proposal ini (misal saat mengedit proposal sendiri).
     * @param list<string>|null  $statuses Status proposal yang dianggap mengunci ruangan.
     * @param bool               $lockForUpdate Kunci baris/gap (SELECT ... FOR UPDATE) agar aman dari race condition; wajib dijalankan di dalam transaksi.
     *
     * @return list<array<string, mixed>> Baris berisi laboratory_id, laboratory_name, dan detail proposal pembentrok.
     */
    function lab_availability_conflicts(
        string $start,
        string $end,
        $laboratoryIds = null,
        ?int $excludeProposalId = null,
        ?array $statuses = null,
        bool $lockForUpdate = false
    ): array {
        $start = lab_availability_normalize_datetime($start);
        $end   = lab_availability_normalize_datetime($end);

        if ($start === '' || $end === '' || strtotime($end) <= strtotime($start)) {
            return [];
        }

        if ($laboratoryIds !== null) {
            $laboratoryIds = array_values(array_unique(array_map('intval', (array) $laboratoryIds)));

            if ($laboratoryIds === []) {
                return [];
            }
        }

        $builder = db_connect()->table('laboratory_loan_proposal_items i')
            ->select('i.laboratory_id, i.id AS item_id, i.notes, l.name AS laboratory_name, p.id AS proposal_id, p.uuid AS proposal_uuid, p.full_name, p.event_name, p.event_start, p.event_end, p.status')
            ->join('laboratory_loan_proposals p', 'p.id = i.proposal_id')
            ->join('laboratories l', 'l.id = i.laboratory_id')
            ->where('i.deleted_at', null)
            ->where('p.deleted_at', null)
            ->whereIn('p.status', $statuses ?? lab_availability_blocking_statuses())
            ->where('p.event_start <', $end)
            ->where('p.event_end >', $start);

        if ($laboratoryIds !== null) {
            $builder->whereIn('i.laboratory_id', $laboratoryIds);
        }

        if ($excludeProposalId !== null) {
            $builder->where('p.id !=', $excludeProposalId);
        }

        $builder->orderBy('p.event_start', 'ASC');

        if ($lockForUpdate) {
            $db = db_connect();

            return $db->query($builder->getCompiledSelect() . ' FOR UPDATE')->getResultArray();
        }

        return $builder->get()->getResultArray();
    }
}

if (! function_exists('lab_availability_map')) {
    /**
     * Peta ketersediaan per laboratorium.
     *
     * @param list<int> $laboratoryIds
     *
     * @return array<int, array{available: bool, conflicts: list<array<string, mixed>>}>
     */
    function lab_availability_map(array $laboratoryIds, string $start, string $end, ?int $excludeProposalId = null): array
    {
        $map = [];

        foreach ($laboratoryIds as $laboratoryId) {
            $map[(int) $laboratoryId] = ['available' => true, 'conflicts' => []];
        }

        foreach (lab_availability_conflicts($start, $end, $laboratoryIds, $excludeProposalId) as $conflict) {
            $laboratoryId = (int) $conflict['laboratory_id'];

            if (! isset($map[$laboratoryId])) {
                $map[$laboratoryId] = ['available' => true, 'conflicts' => []];
            }

            $map[$laboratoryId]['available']    = false;
            $map[$laboratoryId]['conflicts'][] = $conflict;
        }

        return $map;
    }
}

if (! function_exists('lab_is_available')) {
    /**
     * Cek apakah satu laboratorium bebas dari bentrok pada rentang waktu tertentu.
     *
     * @param bool $lockForUpdate Kunci baris pembentrok; jalankan di dalam transaksi untuk mencegah double booking.
     */
    function lab_is_available(int $laboratoryId, string $start, string $end, ?int $excludeProposalId = null, bool $lockForUpdate = false): bool
    {
        return lab_availability_conflicts($start, $end, [$laboratoryId], $excludeProposalId, null, $lockForUpdate) === [];
    }
}

if (! function_exists('lab_available_ids')) {
    /**
     * Daftar id laboratorium aktif yang tersedia pada rentang waktu tertentu.
     *
     * @return list<int>
     */
    function lab_available_ids(string $start, string $end, ?int $excludeProposalId = null): array
    {
        $laboratoryIds = array_map(
            static fn (array $row): int => (int) $row['id'],
            db_connect()->table('laboratories')
                ->select('id')
                ->where('deleted_at', null)
                ->where('status', 'active')
                ->get()
                ->getResultArray()
        );

        $blocked = array_map(
            static fn (array $row): int => (int) $row['laboratory_id'],
            lab_availability_conflicts($start, $end, $laboratoryIds, $excludeProposalId)
        );

        return array_values(array_diff($laboratoryIds, $blocked));
    }
}
