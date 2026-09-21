<?php
/** @var array $requests */
/** @var object $pager */
/** @var string $search */
/** @var string $status */
/** @var array $statuses */
$statusLabels = ['DRAFT' => 'Draft', 'PENDING_REVIEW' => 'Menunggu Review', 'NEED_REVISION' => 'Perlu Revisi', 'APPROVED_BY_KALAB' => 'Disetujui', 'FUND_DISBURSED' => 'Anggaran Cair', 'EVIDEN_SUBMITTED' => 'Eviden Dikirim', 'COMPLETED' => 'Selesai', 'REJECTED' => 'Ditolak'];
$statusColors = ['DRAFT' => 'secondary', 'PENDING_REVIEW' => 'warning', 'NEED_REVISION' => 'warning', 'APPROVED_BY_KALAB' => 'info', 'FUND_DISBURSED' => 'primary', 'EVIDEN_SUBMITTED' => 'info', 'COMPLETED' => 'success', 'REJECTED' => 'danger'];
?>
<div class="page__section flex flex-col gap-4">
  <div class="card"><div class="card__header"><div><span class="card__title">Pengajuan Bahan Habis Pakai</span><div class="text-xs text-muted-foreground">Pengelolaan permintaan, persetujuan, dan pertanggungjawaban BHP</div></div><div class="card__action"><?php if (activeGroupCan('bhp.create')): ?><a class="button button--primary button--sm" href="<?= base_url('bhp/create') ?>">Buat Pengajuan</a><?php endif; ?></div></div>
    <div class="card__body">
      <form method="get" action="<?= base_url('bhp') ?>" style="display:flex;flex-wrap:nowrap;align-items:end;gap:.5rem;overflow-x:auto;padding-bottom:.25rem"><input class="input" style="min-width:18rem" name="q" value="<?= esc($search) ?>" placeholder="Cari kode, laboratorium, pemohon"><select class="input" style="min-width:12rem" name="status"><option value="">Semua status</option><?php foreach ($statuses as $value): ?><option value="<?= esc($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value]) ?></option><?php endforeach; ?></select><button class="button button--outline" type="submit">Filter</button></form>
      <div class="table-responsive" style="margin-top:1rem"><table class="table"><thead><tr><th>Kode</th><th>Laboratorium</th><th>Pemohon</th><th>Periode</th><th>Estimasi</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
      <?php if (empty($requests)): ?><tr><td colspan="7"><?= view('partials/empty_table_state', ['message' => 'Belum ada pengajuan BHP.']) ?></td></tr><?php endif; ?>
      <?php foreach ($requests as $row): ?><tr><td><strong><?= esc($row['kode_pengajuan']) ?></strong></td><td><?= esc($row['laboratory_name']) ?></td><td><?= esc($row['username']) ?></td><td><?= esc($row['nama_periode']) ?></td><td>Rp <?= number_format((float) $row['grand_total_estimasi'], 0, ',', '.') ?></td><td><span class="badge badge--soft badge--<?= esc($statusColors[$row['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$row['status']] ?? $row['status']) ?></span></td><td><a class="button button--info button--icon-only button--sm" href="<?= base_url('bhp/detail/' . $row['uuid']) ?>" title="Detail" aria-label="Detail">Lihat</a><?php if (in_array($row['status'], ['DRAFT', 'NEED_REVISION'], true) && activeGroupCan('bhp.edit')): ?><a class="button button--warning button--icon-only button--sm" href="<?= base_url('bhp/edit/' . $row['uuid']) ?>" title="Edit" aria-label="Edit">Edit</a><?php endif; ?></td></tr><?php endforeach; ?>
      </tbody></table></div>
      <?= $pager->links() ?>
    </div>
  </div>
</div>
