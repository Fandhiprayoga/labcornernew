<?php
/** @var array $proposals */
/** @var CodeIgniter\Pager\Pager $pager */
?>
<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Daftar Proposal Peminjaman</span>
      <div class="card__action">
        <?php if (activeGroupCan('loans.create')): ?>
        <a href="<?= base_url('peminjaman/lab-loans/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Ajukan Proposal
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body" style="border-bottom:1px solid var(--color-border);">
      <form method="get" action="<?= base_url('peminjaman/lab-loans') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 280px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari proposal</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Nomor identitas, nama, atau event...">
        </div>
        <div style="flex:0 0 120px;">
          <label class="text-xs text-muted-foreground" for="perPage">Per halaman</label>
          <select class="select" id="perPage" name="perPage">
            <?php foreach ($perPageOptions as $option): ?><option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?>
          </select>
        </div>
        <div style="display:flex;gap:.5rem;">
          <button type="submit" class="button button--primary button--sm">Filter</button>
          <?php if ($search !== ''): ?>
          <a href="<?= base_url('peminjaman/lab-loans') ?>" class="button button--outline button--sm">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Applicant</th><th>Event</th><th>Tanggal Proposal</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
          <?php if (! empty($proposals)): ?>
            <?php foreach ($proposals as $proposal): ?>
            <?php $statusLabels = ['submitted' => 'Diajukan', 'approved' => 'Disetujui', 'rejected' => 'Ditolak']; $statusColors = ['submitted' => 'warning', 'approved' => 'success', 'rejected' => 'danger']; ?>
            <tr>
              <td><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?> &middot; <?= esc($proposal['email']) ?></div></td>
              <td><strong><?= esc($proposal['event_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc(date('d M Y H:i', strtotime($proposal['event_start']))) ?> - <?= esc(date('d M Y H:i', strtotime($proposal['event_end']))) ?></div></td>
              <td><?= esc(date('d M Y', strtotime($proposal['proposal_date']))) ?></td>
              <td><span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? ucfirst($proposal['status'])) ?></span></td>
              <td class="text-center"><div class="flex justify-center gap-1">
                <?php if ($proposal['status'] === 'submitted' && activeGroupCan('loans.edit')): ?><a href="<?= base_url('peminjaman/lab-loans/edit/' . $proposal['uuid']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                <?php if ($proposal['status'] === 'submitted' && activeGroupCan('loans.delete')): ?><form action="<?= base_url('peminjaman/lab-loans/delete/' . $proposal['uuid']) ?>" method="post" onsubmit="return confirm('Batalkan proposal ini?')"><?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Batalkan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?>
              </div></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => $search !== '' ? 'Data tidak ditemukan untuk filter tersebut.' : 'Belum ada proposal peminjaman.']) ?></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalRows > 0): ?><div class="card__body" style="border-top:1px solid var(--color-border);display:flex;justify-content:space-between;gap:.75rem;"><span class="text-xs text-muted-foreground">Total <?= $totalRows ?> proposal</span><?= $pager->only(['q', 'perPage'])->links('default', 'app') ?></div><?php endif; ?>
  </div>
</div>