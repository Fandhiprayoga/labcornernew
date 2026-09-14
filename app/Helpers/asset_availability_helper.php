<?php

/**
 * Helper ketersediaan asset.
 *
 * Aturan overlap memakai half-open interval:
 * existing.event_start < requested_end AND existing.event_end > requested_start
 * Jadwal yang bersambung tepat pada batas waktu tidak dianggap bentrok.
 */

if (! function_exists('asset_availability_blocking_statuses')) {
    /**
     * Status proposal yang mengunci asset.
     *
     * @return list<string>
     */
    function asset_availability_blocking_statuses(): array
    {
        return ['submitted', 'laboran_approved', 'approved'];
    }
}

if (! function_exists('asset_availability_normalize_datetime')) {
    function asset_availability_normalize_datetime(string $value): string
    {
        $value = str_replace('T', ' ', trim($value));

        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? '' : date('Y-m-d H:i:s', $timestamp);
    }
}

if (! function_exists('asset_availability_conflicts')) {
    /**
     * Ambil proposal yang memakai asset pada rentang waktu yang bentrok.
     *
     * @param list<int>|int|null $assetIds Batasi ke asset tertentu, null = semua.
     * @param int|null $excludeProposalId Abaikan proposal saat mengedit atau memvalidasi ulang.
     * @param list<string>|null $statuses Status proposal yang dianggap mengunci asset.
     * @return list<array<string, mixed>>
     */
    function asset_availability_conflicts(
        string $start,
        string $end,
        $assetIds = null,
        ?int $excludeProposalId = null,
        ?array $statuses = null
    ): array {
        $start = asset_availability_normalize_datetime($start);
        $end   = asset_availability_normalize_datetime($end);

        if ($start === '' || $end === '' || strtotime($end) <= strtotime($start)) {
            return [];
        }

        if ($assetIds !== null) {
            $assetIds = array_values(array_unique(array_map('intval', (array) $assetIds)));

            if ($assetIds === []) {
                return [];
            }
        }

        $builder = db_connect()->table('asset_loan_proposal_items i')
            ->select('i.asset_id, i.id AS item_id, i.notes, a.asset_code, a.name AS asset_name, p.id AS proposal_id, p.uuid AS proposal_uuid, p.full_name, p.event_name, p.event_start, p.event_end, p.status')
            ->join('asset_loan_proposals p', 'p.id = i.proposal_id')
            ->join('assets a', 'a.id = i.asset_id')
            ->where('i.deleted_at', null)
            ->where('p.deleted_at', null)
            ->whereIn('p.status', $statuses ?? asset_availability_blocking_statuses())
            ->where('p.event_start <', $end)
            ->where('p.event_end >', $start);

        if ($assetIds !== null) {
            $builder->whereIn('i.asset_id', $assetIds);
        }

        if ($excludeProposalId !== null) {
            $builder->where('p.id !=', $excludeProposalId);
        }

        return $builder->orderBy('p.event_start', 'ASC')->get()->getResultArray();
    }
}

if (! function_exists('asset_is_available')) {
    /**
     * Cek apakah asset bebas pada rentang waktu event.
     */
    function asset_is_available(int $assetId, string $start, string $end, ?int $excludeProposalId = null): bool
    {
        return asset_availability_conflicts($start, $end, [$assetId], $excludeProposalId) === [];
    }
}

if (! function_exists('asset_availability_blocked_ids')) {
    /**
     * Ambil id asset yang bentrok pada rentang waktu event.
     *
     * @return list<int>
     */
    function asset_availability_blocked_ids(string $start, string $end, ?int $excludeProposalId = null): array
    {
        return array_values(array_unique(array_map(
            static fn (array $row): int => (int) $row['asset_id'],
            asset_availability_conflicts($start, $end, null, $excludeProposalId)
        )));
    }
}