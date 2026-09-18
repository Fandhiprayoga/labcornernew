<?php
/** @var array $proposals */
/** @var array $summary */
/** @var array $laboratorySummary */
/** @var array $topAssets */
/** @var array $overdueItems */
/** @var CodeIgniter\Pager\Pager $pager */
$statusLabels = ['submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'cancelled' => 'Dibatalkan', 'completed' => 'Selesai'];
$statusColors = ['submitted' => 'warning', 'laboran_approved' => 'info', 'rejected' => 'danger', 'approved' => 'success', 'cancelled' => 'danger', 'completed' => 'primary'];
$query = ['q' => $q, 'start_date' => $startDate, 'end_date' => $endDate, 'laboratory_uuid' => $laboratoryUuid, 'status' => $status];
?>
<div class="page__section">
  <div class="card">
    <div class="card__header"><span class="card__title">Filter Laporan</span>
      <?php if (activeGroupCan('reports.export')): ?><div class="card__action"><a class="button button--info button--sm" href="<?= base_url('peminjaman/asset-report/export/csv?' . http_build_query($query)) ?>">Export CSV</a></div><?php endif; ?>
    </div>
    <div class="card__body">
      <form method="get" action="<?= base_url('peminjaman/asset-report') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 220px;min-width:180px;"><label class="text-xs text-muted-foreground" for="q">Cari</label><input class="input" type="search" id="q" name="q" value="<?= esc($q) ?>" placeholder="Pemohon, kegiatan, atau asset..."></div>
        <div style="flex:0 1 170px;min-width:150px;"><label class="text-xs text-muted-foreground" for="start_date">Mulai</label><input class="input" type="date" id="start_date" name="start_date" value="<?= esc($startDate) ?>"></div>
        <div style="flex:0 1 170px;min-width:150px;"><label class="text-xs text-muted-foreground" for="end_date">Selesai</label><input class="input" type="date" id="end_date" name="end_date" value="<?= esc($endDate) ?>"></div>
        <div style="flex:0 1 220px;min-width:180px;"><label class="text-xs text-muted-foreground" for="laboratory_uuid">Laboratorium</label><select class="select" id="laboratory_uuid" name="laboratory_uuid"><option value="">Semua Laboratorium</option><?php foreach ($laboratoryOptions as $option): ?><option value="<?= esc($option['uuid']) ?>" <?= $laboratoryUuid === $option['uuid'] ? 'selected' : '' ?>><?= esc($option['name']) ?></option><?php endforeach; ?></select></div>
        <div style="flex:0 1 190px;min-width:160px;"><label class="text-xs text-muted-foreground" for="status">Status</label><select class="select" id="status" name="status"><option value="">Semua Status</option><?php foreach ($statusOptions as $option): ?><option value="<?= esc($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc($statusLabels[$option]) ?></option><?php endforeach; ?></select></div>
        <div style="display:flex;gap:.5rem;"><button type="submit" class="button button--primary button--sm">Filter</button><a href="<?= base_url('peminjaman/asset-report') ?>" class="button button--outline button--sm">Reset</a></div>
      </form>
    </div>
  </div>
</div>

<div class="page__section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem;">
  <?php foreach (['total' => 'Total Pengajuan', 'approved' => 'Disetujui', 'completed' => 'Selesai', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'] as $key => $label): ?>
  <div class="card"><div class="card__body"><div class="text-xs text-muted-foreground"><?= $label ?></div><strong style="font-size:1.5rem;"><?= $summary[$key] ?></strong></div></div>
  <?php endforeach; ?>
</div>

<div class="page__section" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:1rem;">
  <div class="card">
    <div class="card__header"><span class="card__title">Rekap per Laboratorium</span></div>
    <div class="card__body p-0"><div class="table-responsive"><table class="table"><thead><tr><th>Laboratorium</th><th class="text-end">Pengajuan</th><th class="text-end">Asset Dipinjam</th></tr></thead><tbody>
      <?php foreach ($laboratorySummary as $item): ?><tr><td><?= esc($item['laboratory_name'] ?: '-') ?></td><td class="text-end"><?= $item['total_loans'] ?></td><td class="text-end"><?= $item['total_items'] ?></td></tr><?php endforeach; ?>
      <?php if (empty($laboratorySummary)): ?><tr><td colspan="3" class="text-center text-muted-foreground py-8">Tidak ada data pada periode ini.</td></tr><?php endif; ?>
    </tbody></table></div></div>
  </div>

  <div class="card">
    <div class="card__header"><span class="card__title">Asset Paling Sering Dipinjam</span></div>
    <div class="card__body p-0"><div class="table-responsive"><table class="table"><thead><tr><th>Asset</th><th>Laboratorium</th><th class="text-end">Total Dipinjam</th></tr></thead><tbody>
      <?php foreach ($topAssets as $item): ?><tr><td><strong><?= esc($item['asset_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($item['asset_code']) ?></div></td><td><?= esc($item['laboratory_name'] ?: '-') ?></td><td class="text-end"><?= $item['total_borrowed'] ?></td></tr><?php endforeach; ?>
      <?php if (empty($topAssets)): ?><tr><td colspan="3" class="text-center text-muted-foreground py-8">Tidak ada data pada periode ini.</td></tr><?php endif; ?>
    </tbody></table></div></div>
  </div>
</div>

<?php if (! empty($overdueItems)): ?>
<div class="page__section"><div class="card">
  <div class="card__header"><span class="card__title">Asset Terlambat Dikembalikan</span><div class="card__action"><span class="badge badge--soft badge--danger"><?= count($overdueItems) ?> item</span></div></div>
  <div class="card__body p-0"><div class="table-responsive"><table class="table"><thead><tr><th>Peminjam</th><th>Kegiatan</th><th>Asset</th><th>Laboratorium</th><th>Batas Kembali</th></tr></thead><tbody>
    <?php foreach ($overdueItems as $item): ?><tr><td><?= esc($item['full_name']) ?></td><td><?= esc($item['event_name']) ?></td><td><strong><?= esc($item['asset_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($item['asset_code']) ?></div></td><td><?= esc($item['laboratory_name'] ?: '-') ?></td><td><span class="badge badge--soft badge--danger"><?= esc(date('d M Y H:i', strtotime($item['event_end']))) ?></span></td></tr><?php endforeach; ?>
  </tbody></table></div></div>
</div></div>
<?php endif; ?>

<div class="page__section"><div class="card"><div class="card__header"><span class="card__title">Transaksi Peminjaman</span></div><div class="card__body p-0"><div class="table-responsive"><table class="table"><thead><tr><th>Pemohon / Kegiatan</th><th>Asset</th><th>Jadwal</th><th>Durasi</th><th>Status</th></tr></thead><tbody><?php foreach ($proposals as $proposal): ?><tr><td><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['event_name']) ?></div></td><td><?= esc($proposal['asset_names'] ?: '-') ?><div class="text-xs text-muted-foreground"><?= $proposal['total_items'] ?> asset</div></td><td><?= esc(date('d M Y H:i', strtotime($proposal['event_start']))) ?><div class="text-xs text-muted-foreground">s.d. <?= esc(date('d M Y H:i', strtotime($proposal['event_end']))) ?></div></td><td><?= esc($proposal['duration_hours']) ?> jam</td><td><span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? $proposal['status']) ?></span></td></tr><?php endforeach; ?><?php if (empty($proposals)): ?><tr><td colspan="5" class="text-center text-muted-foreground py-8">Tidak ada transaksi pada periode ini.</td></tr><?php endif; ?></tbody></table></div></div><?php if ($totalRows > 0): ?><div class="card__body" style="border-top:1px solid var(--color-border);display:flex;justify-content:space-between;align-items:center;gap:.75rem;"><span class="text-xs text-muted-foreground">Total <?= $totalRows ?> transaksi</span><?= $pager->only(['q', 'start_date', 'end_date', 'laboratory_uuid', 'status', 'perPage'])->links('default', 'app') ?></div><?php endif; ?></div></div>
