<?php
/** @var array $requests */
/** @var object $pager */
/** @var string $search */
/** @var string $status */
/** @var int $periodId */
/** @var array $periods */
/** @var array $statuses */
/** @var array $availablePrograms */
$statusLabels = ['DRAFT' => 'Draft', 'PENDING_REVIEW' => 'Menunggu Review', 'NEED_REVISION' => 'Perlu Revisi', 'APPROVED_BY_KALAB' => 'Disetujui', 'FUND_DISBURSED' => 'Anggaran Cair', 'EVIDEN_SUBMITTED' => 'Eviden Dikirim', 'COMPLETED' => 'Selesai', 'REJECTED' => 'Ditolak'];
$statusColors = ['DRAFT' => 'secondary', 'PENDING_REVIEW' => 'warning', 'NEED_REVISION' => 'warning', 'APPROVED_BY_KALAB' => 'info', 'FUND_DISBURSED' => 'primary', 'EVIDEN_SUBMITTED' => 'info', 'COMPLETED' => 'success', 'REJECTED' => 'danger'];
?>
<div class="page__section flex flex-col gap-4">
  <div class="card"><div class="card__header"><div><span class="card__title">Pengajuan BHP Program Studi</span><div class="text-xs text-muted-foreground">Pilih program studi untuk menambahkan item ke pengajuan BHP.</div></div></div>
    <div class="card__body">
      <form method="get" action="<?= base_url('bhp') ?>" style="display:flex;flex-wrap:nowrap;align-items:end;gap:.5rem;overflow-x:auto;padding-bottom:.25rem"><input class="input" style="min-width:18rem" name="q" value="<?= esc($search) ?>" placeholder="Cari kode pengajuan, program studi, atau laboratorium"><select class="input" style="min-width:12rem" name="status"><option value="">Semua status</option><?php foreach ($statuses as $value): ?><option value="<?= esc($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value]) ?></option><?php endforeach; ?></select><select class="input" style="min-width:16rem" name="periode_id"><option value="">Semua periode</option><?php foreach ($periods as $period): ?><option value="<?= (int) $period['id'] ?>" <?= $periodId === (int) $period['id'] ? 'selected' : '' ?>><?= esc($period['nama_periode']) ?></option><?php endforeach; ?></select><button class="button button--outline" type="submit">Filter</button></form>
      <div class="table-responsive" style="margin-top:1rem"><table class="table"><thead><tr><th>Pengajuan</th><th>Program Studi</th><th>Periode</th><th>Estimasi</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
      <?php if (empty($requests)): ?><tr><td colspan="6"><?= view('partials/empty_table_state', ['message' => 'Belum ada pengajuan BHP.']) ?></td></tr><?php endif; ?>
      <?php foreach ($requests as $row): ?><tr><td><strong><?= esc($row['kode_pengajuan']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($row['nama_lab_snapshot']) ?></div></td><td><?= esc($row['study_program_name'] ?? $row['prodi_snapshot'] ?? '-') ?></td><td><?= esc($row['nama_periode']) ?></td><td>Rp <?= number_format((float) $row['grand_total_estimasi'], 0, ',', '.') ?></td><td><span class="badge badge--soft badge--<?= esc($statusColors[$row['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$row['status']] ?? $row['status']) ?></span></td><td class="text-end"><div class="flex justify-end gap-1"><?php if (activeGroupCan('bhp.create') && in_array($row['status'], ['DRAFT', 'NEED_REVISION'], true)): ?><a class="button button--primary button--icon-only button--sm" href="<?= base_url('bhp/create?pocket_uuid=' . $row['uuid']) ?>" title="Tambah item" aria-label="Tambah item"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg></a><?php endif; ?><a class="button button--info button--icon-only button--sm" href="<?= base_url('bhp/detail/' . $row['uuid']) ?>" title="Detail" aria-label="Lihat detail pengajuan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.5" /></svg></a><?php if (in_array($row['status'], ['DRAFT', 'NEED_REVISION'], true) && activeGroupCan('bhp.edit') && ! empty($row['laboran_id'])): ?><a class="button button--warning button--icon-only button--sm" href="<?= base_url('bhp/edit/' . $row['uuid']) ?>" title="Edit" aria-label="Edit pengajuan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" /></svg></a><?php endif; ?></div></td></tr><?php endforeach; ?>
      </tbody></table></div>
      <?= $pager->links() ?>
    </div>
  </div>
</div>
