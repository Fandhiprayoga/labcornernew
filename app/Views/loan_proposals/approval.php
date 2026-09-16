<?php
/** @var array $proposals */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var string $search */
/** @var string $status */
/** @var string[] $statusOptions */
/** @var string $laboratoryUuid */
/** @var array $laboratoryOptions */
/** @var int $perPage */
/** @var int[] $perPageOptions */
/** @var string $stage */
/** @var array $history */
/** @var string $tab */
$statusLabels = [
  'draft' => 'Draft',
    'submitted' => 'Menunggu Approval Laboran',
    'laboran_approved' => 'Menunggu Approval Kepala Lab',
  'approved' => 'Disetujui',
  'rejected' => 'Ditolak',
  'completed' => 'Selesai',
];
$statusColors = [
  'submitted' => 'warning',
  'laboran_approved' => 'info',
];
$stageTitle = $stage === 'laboran'
    ? 'Menunggu Approval Laboran'
  : ($stage === 'kepala_lab' ? 'Menunggu Approval Kepala Lab' : ($stage === 'history' ? 'Riwayat Approval' : 'Daftar Approval'));
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
      <div class="toggle-group" data-stisla-toggle-group role="radiogroup" aria-label="Jenis data approval" style="display:inline-flex;width:max-content;max-width:100%;overflow-x:auto;">
        <a href="<?= base_url('peminjaman/lab-loans-approval?tab=pending') ?>" class="toggle" role="radio" aria-checked="<?= $tab === 'pending' ? 'true' : 'false' ?>" data-state="<?= $tab === 'pending' ? 'active' : 'inactive' ?>">Proposal Butuh Approval</a>
        <a href="<?= base_url('peminjaman/lab-loans-approval?tab=history') ?>" class="toggle" role="radio" aria-checked="<?= $tab === 'history' ? 'true' : 'false' ?>" data-state="<?= $tab === 'history' ? 'active' : 'inactive' ?>">History Approval</a>
      </div>
    </div>
    <div class="card__body" style="border-bottom:1px solid var(--color-border);">
      <form method="get" action="<?= base_url('peminjaman/lab-loans-approval') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <input type="hidden" name="tab" value="<?= esc($tab) ?>">
        <div style="flex:1 1 260px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari proposal</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Nomor identitas, nama, kegiatan, atau laboratorium...">
        </div>
        <div style="flex:0 1 220px;min-width:180px;">
          <label class="text-xs text-muted-foreground" for="laboratory_uuid">Laboratorium</label>
          <select class="select" id="laboratory_uuid" name="laboratory_uuid">
            <?php if (! activeGroupIs('laboran')): ?>
            <option value="">Semua Laboratorium</option>
            <?php endif; ?>
            <?php foreach ($laboratoryOptions as $option): ?>
            <option value="<?= esc($option['uuid']) ?>" <?= $laboratoryUuid === (string) ($option['uuid'] ?? '') ? 'selected' : '' ?>><?= esc($option['name']) ?><?= ! empty($option['room_code']) ? ' (' . esc($option['room_code']) . ')' : '' ?></option>
            <?php endforeach; ?>
          </select>
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
          <?php if ($search !== '' || $status !== '' || $laboratoryUuid !== ''): ?><a href="<?= base_url('peminjaman/lab-loans-approval') ?>" class="button button--outline button--sm">Reset</a><?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
    <?php if ($tab === 'history'): ?>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Waktu</th><th>Pemohon</th><th>Kegiatan</th><th>Keputusan</th><th>Oleh</th><th>Keterangan</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php if (! empty($history)): ?>
            <?php foreach ($history as $entry): ?>
            <tr>
              <td><?= esc($fmt($entry['created_at'])) ?></td>
              <td><strong><?= esc($entry['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($entry['identity_number']) ?></div></td>
              <td><strong><?= esc($entry['event_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($entry['laboratory_name']) ?></div></td>
              <td><span class="badge badge--soft badge--<?= $entry['to_status'] === 'rejected' ? 'danger' : 'success' ?>"><?= esc($statusLabels[$entry['to_status']] ?? $entry['to_status']) ?></span></td>
              <td><?= esc($entry['changed_by_name'] ?: '-') ?></td>
              <td><?= esc($entry['note'] ?: '-') ?></td>
              <td class="text-end"><a href="<?= base_url('peminjaman/lab-loans/detail-approval-history/' . $entry['proposal_uuid']) ?>" class="button button--info button--icon-only button--sm" title="Detail Proposal" aria-label="Detail Proposal"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5a7.5 7.5 0 1 0 0 15a7.5 7.5 0 0 0 0-15Zm0 3.25v.5m0 2.5v4.5" /></svg></a></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => 'Belum ada history approval.']) ?></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Pemohon</th><th>Kegiatan</th><th>Laboratorium</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php if (! empty($proposals)): ?>
            <?php foreach ($proposals as $proposal): ?>
            <tr>
              <td><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?></div></td>
              <td><strong><?= esc($proposal['event_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($fmt($proposal['event_start'])) ?> - <?= esc($fmt($proposal['event_end'])) ?></div></td>
              <td><strong><?= esc($proposal['laboratory_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc(($proposal['room_code'] ?? '-') . ' - ' . ($proposal['room_name'] ?? '-')) ?></div></td>
              <td><span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? $proposal['status']) ?></span></td>
              <td class="text-end">
                <div class="flex justify-end gap-1">
                  <a href="<?= base_url('peminjaman/lab-loans/detail-approval/' . $proposal['uuid']) ?>" class="button button--info button--icon-only button--sm" title="Detail Proposal" aria-label="Detail Proposal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5a7.5 7.5 0 1 0 0 15a7.5 7.5 0 0 0 0-15Zm0 3.25v.5m0 2.5v4.5" /></svg>
                  </a>
                  <button type="button" class="button button--success button--icon-only button--sm" title="Setujui" aria-label="Setujui"
                    onclick="openApprovalDialog('approveConfirm', '<?= esc(base_url('peminjaman/lab-loans-approval/' . $proposal['uuid'] . '/approve'), 'js') ?>', '<?= esc($proposal['event_name'], 'js') ?>')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m5 12 4 4L19 6" /></svg>
                  </button>
                  <button type="button" class="button button--danger button--icon-only button--sm" title="Tolak" aria-label="Tolak"
                    onclick="openApprovalDialog('rejectConfirm', '<?= esc(base_url('peminjaman/lab-loans-approval/' . $proposal['uuid'] . '/reject'), 'js') ?>', '<?= esc($proposal['event_name'], 'js') ?>')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.75" d="m7 7 10 10M17 7 7 17" /></svg>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => 'Tidak ada proposal yang menunggu approval pada tahap ini.']) ?></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
    </div>
    <?php if ($pager->getTotal() > 0): ?><div class="card__body" style="border-top:1px solid var(--color-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;"><span class="text-xs text-muted-foreground">Total <?= $pager->getTotal() ?> <?= $tab === 'history' ? 'history approval' : 'proposal' ?></span><?= $pager->only(['tab', 'q', 'status', 'laboratory_uuid', 'perPage'])->links('default', 'app') ?></div><?php endif; ?>
  </div>
</div>

<!-- Dialog: Approve -->
<div class="dialog dialog--sm" id="approveConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="approveConfirmLabel" aria-describedby="approveConfirmDesc" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
      </button>
      <div class="dialog__body text-center pt-6">
        <span class="icon-box icon-box--success icon-box--circle icon-box--lg mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m5 12 4 4L19 6" /></svg>
        </span>
        <h3 class="dialog__title mb-1" id="approveConfirmLabel">Setujui proposal ini?</h3>
        <p class="text-muted-foreground" id="approveConfirmDesc">Proposal <strong data-slot="approve-event"></strong> akan lanjut ke tahap persetujuan berikutnya.</p>
      </div>
      <form id="approveForm" method="post">
        <?= csrf_field() ?>
        <div class="dialog__footer justify-center">
          <button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
          <button type="submit" class="button button--success">Ya, Setujui</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Dialog: Reject -->
<div class="dialog dialog--sm" id="rejectConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="rejectConfirmLabel" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <div class="dialog__header">
        <h3 class="dialog__title" id="rejectConfirmLabel">Tolak Proposal</h3>
        <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
        </button>
      </div>
      <form id="rejectForm" method="post">
        <?= csrf_field() ?>
        <div class="dialog__body">
          <p class="text-muted-foreground text-sm mb-4">Anda akan menolak proposal <strong data-slot="reject-event"></strong>. Alasan ini akan terlihat oleh pemohon dan tahap approval sebelumnya.</p>
          <div class="field">
            <label class="field__label" for="reject_note">Alasan Penolakan <span class="text-danger">*</span></label>
            <textarea class="input" id="reject_note" name="note" rows="3" required placeholder="Tuliskan alasan penolakan proposal ini..."></textarea>
          </div>
        </div>
        <div class="dialog__footer">
          <button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
          <button type="submit" class="button button--danger">Tolak Proposal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openApprovalDialog(dialogId, actionUrl, eventName) {
    var dialog = document.getElementById(dialogId);
    if (!dialog) return;
    var form = dialog.querySelector('form');
    form.action = actionUrl;
    var slot = dialog.querySelector('[data-slot="' + (dialogId === 'approveConfirm' ? 'approve-event' : 'reject-event') + '"]');
    if (slot) slot.textContent = eventName;
    if (dialogId === 'rejectConfirm') {
      var note = form.querySelector('#reject_note');
      note.value = '';
    }
    dialog.dataset.state = 'open';
    dialog.setAttribute('aria-hidden', 'false');
    window.requestAnimationFrame(function () {
      var focusTarget = dialog.querySelector('#reject_note') || dialog.querySelector('[data-stisla-dialog-dismiss]');
      if (focusTarget) focusTarget.focus();
    });
  }

  document.addEventListener('click', function (event) {
    if (!event.target.closest('[data-stisla-dialog-dismiss]')) return;
    var dialog = event.target.closest('[data-stisla-dialog]');
    if (!dialog) return;
    dialog.dataset.state = 'closed';
    dialog.setAttribute('aria-hidden', 'true');
  });

  document.querySelectorAll('#approveForm, #rejectForm').forEach(function (form) {
    form.addEventListener('submit', function () {
      var submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;
    });
  });
</script>
