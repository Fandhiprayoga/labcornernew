<?php
/** @var array $proposals */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var string $search */
/** @var string $status */
/** @var string[] $statusOptions */
/** @var int $perPage */
/** @var int[] $perPageOptions */
/** @var string $stage */
$statusLabels = [
    'submitted' => 'Menunggu Approval Laboran',
    'laboran_approved' => 'Menunggu Approval Kepala Lab',
];
$statusColors = [
  'submitted' => 'warning',
  'laboran_approved' => 'info',
];
$stageTitle = $stage === 'laboran'
    ? 'Menunggu Approval Laboran'
    : ($stage === 'kepala_lab' ? 'Menunggu Approval Kepala Lab' : 'Daftar Approval');
$fmt = static fn (string $value): string => date('d M Y H:i', strtotime($value));
?>
<div class="page__section flex flex-col gap-4">
  <div class="card">
    <div class="card__header">
      <div>
        <span class="card__title">Persetujuan Peminjaman Laboratorium</span>
        <div class="text-xs text-muted-foreground"><?= esc($stageTitle) ?></div>
      </div>
      <div class="card__action">
        <a href="<?= base_url('peminjaman/lab-loans') ?>" class="button button--outline button--neutral button--sm">Daftar Proposal</a>
      </div>
    </div>
    <div class="card__body" style="border-bottom:1px solid var(--color-border);">
      <form method="get" action="<?= base_url('peminjaman/lab-loans-approval') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 280px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari proposal</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Nomor identitas, nama, kegiatan, atau laboratorium...">
        </div>
        <div style="flex:0 0 220px;">
          <label class="text-xs text-muted-foreground" for="status">Status Approval</label>
          <select class="select" id="status" name="status">
            <option value="">Semua Status</option>
            <?php foreach ($statusOptions as $option): ?><option value="<?= esc($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc($statusLabels[$option]) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div style="flex:0 0 120px;">
          <label class="text-xs text-muted-foreground" for="perPage">Per halaman</label>
          <select class="select" id="perPage" name="perPage">
          <?php foreach ($perPageOptions as $option): ?><option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?>
          </select>
        </div>
        <div style="display:flex;gap:.5rem;">
          <button type="submit" class="button button--primary button--sm">Filter</button>
          <?php if ($search !== '' || $status !== ''): ?><a href="<?= base_url('peminjaman/lab-loans-approval') ?>" class="button button--outline button--sm">Reset</a><?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Pemohon</th><th>Kegiatan</th><th>Laboratorium</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
          <?php if (! empty($proposals)): ?>
            <?php foreach ($proposals as $proposal): ?>
            <tr>
              <td><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?></div></td>
              <td><strong><?= esc($proposal['event_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($fmt($proposal['event_start'])) ?> - <?= esc($fmt($proposal['event_end'])) ?></div></td>
              <td><strong><?= esc($proposal['laboratory_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc(($proposal['room_code'] ?? '-') . ' - ' . ($proposal['room_name'] ?? '-')) ?></div></td>
              <td><span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? $proposal['status']) ?></span></td>
              <td class="text-center">
                <div class="flex justify-center gap-1">
                  <a href="<?= base_url('peminjaman/lab-loans/detail/' . $proposal['uuid']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Detail Proposal" aria-label="Detail Proposal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5a7.5 7.5 0 1 0 0 15a7.5 7.5 0 0 0 0-15Zm0 3.25v.5m0 2.5v4.5" /></svg>
                  </a>
                  <form action="<?= base_url('peminjaman/lab-loans-approval/' . $proposal['uuid'] . '/approve') ?>" method="post" onsubmit="return confirm('Setujui proposal ini?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="button button--ghost button--success button--icon-only button--sm" title="Setujui" aria-label="Setujui">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m5 12 4 4L19 6" /></svg>
                    </button>
                  </form>
                  <button type="button" class="button button--ghost button--danger button--icon-only button--sm" title="Tolak" aria-label="Tolak"
                    onclick="document.getElementById('reject-form-<?= esc($proposal['uuid'], 'attr') ?>').classList.remove('hidden')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.75" d="m7 7 10 10M17 7 7 17" /></svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr id="reject-form-<?= esc($proposal['uuid'], 'attr') ?>" class="hidden">
              <td colspan="5">
                <form action="<?= base_url('peminjaman/lab-loans-approval/' . $proposal['uuid'] . '/reject') ?>" method="post" onsubmit="return confirm('Tolak proposal ini?')" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
                  <?= csrf_field() ?>
                  <div style="flex:1 1 320px;min-width:220px;">
                    <label class="text-xs text-muted-foreground" for="note-<?= esc($proposal['uuid'], 'attr') ?>">Alasan penolakan</label>
                    <textarea class="input" id="note-<?= esc($proposal['uuid'], 'attr') ?>" name="note" rows="2" required placeholder="Tuliskan alasan penolakan proposal ini..."></textarea>
                  </div>
                  <div style="display:flex;gap:.5rem;">
                    <button type="submit" class="button button--danger button--sm">Tolak Proposal</button>
                    <button type="button" class="button button--outline button--sm"
                      onclick="document.getElementById('reject-form-<?= esc($proposal['uuid'], 'attr') ?>').classList.add('hidden')">Batal</button>
                  </div>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => 'Tidak ada proposal yang menunggu approval pada tahap ini.']) ?></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($pager->getTotal() > 0): ?><div class="card__body" style="border-top:1px solid var(--color-border);display:flex;justify-content:space-between;gap:.75rem;"><span class="text-xs text-muted-foreground">Total <?= $pager->getTotal() ?> proposal</span><?= $pager->only(['q', 'status', 'perPage'])->links('default', 'app') ?></div><?php endif; ?>
  </div>
</div>
