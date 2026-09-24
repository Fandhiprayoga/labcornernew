<?php
/** @var array $requests */
/** @var object $pager */
/** @var string $search */
/** @var string $status */
/** @var int $periodId */
/** @var array $periods */
/** @var array $statuses */
/** @var array $availablePrograms */
/** @var int $perPage */
/** @var array $perPageOptions */
/** @var int $currentPage */
/** @var int $totalRows */
$statusLabels = ['DRAFT' => 'Draft', 'PENDING_REVIEW' => 'Menunggu Review', 'NEED_REVISION' => 'Perlu Revisi', 'APPROVED_BY_KALAB' => 'Disetujui', 'FUND_DISBURSED' => 'Anggaran Cair', 'EVIDEN_SUBMITTED' => 'Eviden Dikirim', 'COMPLETED' => 'Selesai', 'REJECTED' => 'Ditolak'];
$statusColors = ['DRAFT' => 'secondary', 'PENDING_REVIEW' => 'warning', 'NEED_REVISION' => 'warning', 'APPROVED_BY_KALAB' => 'info', 'FUND_DISBURSED' => 'primary', 'EVIDEN_SUBMITTED' => 'info', 'COMPLETED' => 'success', 'REJECTED' => 'danger'];
?>
<div class="page__section flex flex-col gap-4">
  <div class="card"><div class="card__header"><div><span class="card__title">Pengajuan BHP Program Studi</span><div class="text-xs text-muted-foreground">Pilih program studi untuk menambahkan item ke pengajuan BHP.</div></div></div>
    <div class="card__body">
      <form method="get" action="<?= base_url('bhp') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 260px;min-width:220px;"><label class="text-xs text-muted-foreground" for="q">Cari</label><input class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Kode pengajuan, program studi, atau laboratorium"></div>
        <div style="flex:0 1 190px;min-width:170px;"><label class="text-xs text-muted-foreground" for="status">Status</label><select class="select" id="status" name="status"><option value="">Semua status</option><?php foreach ($statuses as $value): ?><option value="<?= esc($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= esc($statusLabels[$value]) ?></option><?php endforeach; ?></select></div>
        <div style="flex:0 1 220px;min-width:190px;"><label class="text-xs text-muted-foreground" for="periode_id">Periode</label><select class="select" id="periode_id" name="periode_id"><option value="">Semua periode</option><?php foreach ($periods as $period): ?><option value="<?= (int) $period['id'] ?>" <?= $periodId === (int) $period['id'] ? 'selected' : '' ?>><?= esc($period['nama_periode']) ?></option><?php endforeach; ?></select></div>
        <div style="flex:0 0 110px;"><label class="text-xs text-muted-foreground" for="perPage">Per Halaman</label><select class="select" id="perPage" name="perPage"><?php foreach ($perPageOptions as $option): ?><option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?></select></div>
        <button class="button button--primary button--sm" type="submit">Filter</button>
      </form>
      <div class="table-responsive" style="margin-top:1rem"><table class="table"><thead><tr><th>Pengajuan</th><th>Program Studi</th><th>Periode</th><th>Estimasi</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
      <?php if (empty($requests)): ?><tr><td colspan="6"><?= view('partials/empty_table_state', ['message' => 'Belum ada pengajuan BHP.']) ?></td></tr><?php endif; ?>
      <?php foreach ($requests as $row): ?>
        <?php $editableItemCount = (int) ($row['editable_item_count'] ?? 0); ?>
        <tr>
          <td><strong><?= esc($row['kode_pengajuan']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($row['uuid']) ?></div></td>
          <td><?= esc($row['study_program_name'] ?? $row['prodi_snapshot'] ?? '-') ?></td>
          <td><?= esc($row['nama_periode']) ?></td>
          <td>Rp <?= number_format((float) $row['grand_total_estimasi'], 0, ',', '.') ?></td>
          <td><span class="badge badge--soft badge--<?= esc($statusColors[$row['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$row['status']] ?? $row['status']) ?></span></td>
          <td class="text-end">
            <div class="flex justify-end gap-1">
              <?php if (activeGroupCan('bhp.create') && in_array($row['status'], ['DRAFT', 'NEED_REVISION'], true)): ?>
                <a class="button button--primary button--icon-only button--sm" href="<?= base_url('bhp/create?pocket_uuid=' . $row['uuid']) ?>" title="Tambah item" aria-label="Tambah item"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg></a>
              <?php endif; ?>
              <a class="button button--info button--icon-only button--sm" href="<?= base_url('bhp/detail/' . $row['uuid']) ?>" title="Detail" aria-label="Lihat detail pengajuan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.5 12s3.5-6 9.5-6 9.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.5" /></svg></a>
              <?php if (in_array($row['status'], ['DRAFT', 'NEED_REVISION'], true) && activeGroupCan('bhp.edit')): ?>
                <?php if ($editableItemCount > 0): ?>
                  <a class="button button--warning button--icon-only button--sm" href="<?= base_url('bhp/edit/' . $row['uuid']) ?>" title="Edit" aria-label="Edit pengajuan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" /></svg></a>
                <?php else: ?>
                  <button type="button" class="button button--warning button--icon-only button--sm" title="Belum ada item untuk diedit" aria-label="Belum ada item untuk diedit" disabled><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" /></svg></button>
                <?php endif; ?>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody></table></div>
      <?php if ($totalRows > 0): ?>
      <div class="card__body" style="border-top:1px solid var(--color-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;">
        <div class="text-xs text-muted-foreground">Menampilkan <?= $requests ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($requests) ?> dari <?= $totalRows ?> data</div>
        <?= $pager->only(['q', 'status', 'periode_id', 'perPage'])->links('default', 'app') ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
