<?php
/** @var array $proposals */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var string $search */
/** @var int $perPage */
/** @var int[] $perPageOptions */
/** @var int $totalRows */
/** @var string $status */
/** @var string[] $statusOptions */
/** @var string $laboratoryUuid */
/** @var array $laboratoryOptions */
/** @var string $tab */
$statusLabels = ['draft' => 'Draft', 'submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'cancelled' => 'Dibatalkan', 'completed' => 'Selesai'];
?>
<style>
  .loan-proposal-tabs { display:flex; gap:.25rem; border-bottom:1px solid var(--color-border); }
  .loan-proposal-tabs__link { padding:.75rem 1rem; border-bottom:2px solid transparent; color:var(--color-muted-foreground); font-size:.875rem; font-weight:600; }
  .loan-proposal-tabs__link[data-active="true"] { border-bottom-color:var(--color-primary); color:var(--color-primary); }
</style>
<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Daftar Pengajuan Peminjaman</span>
      <div class="card__action">
        <?php if (activeGroupCan('loans.create')): ?>
        <a href="<?= base_url('peminjaman/lab-loans/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Buat Pengajuan
        </a>
        <?php endif; ?>
      </div>
    </div>
    <nav class="loan-proposal-tabs" aria-label="Kategori pengajuan peminjaman laboratorium">
      <a class="loan-proposal-tabs__link" data-active="<?= $tab === 'active' ? 'true' : 'false' ?>" href="<?= base_url('peminjaman/lab-loans?tab=active') ?>">Pengajuan Aktif</a>
      <a class="loan-proposal-tabs__link" data-active="<?= $tab === 'archive' ? 'true' : 'false' ?>" href="<?= base_url('peminjaman/lab-loans?tab=archive') ?>">Arsip Pengajuan</a>
    </nav>
    <div class="card__body" style="border-bottom:1px solid var(--color-border);">
      <form method="get" action="<?= base_url('peminjaman/lab-loans') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <input type="hidden" name="tab" value="<?= esc($tab) ?>">
        <div style="flex:1 1 260px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari pengajuan</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Nomor identitas, nama, event, atau laboratorium...">
        </div>
        <div style="flex:0 1 220px;min-width:180px;">
          <label class="text-xs text-muted-foreground" for="laboratory_uuid">Laboratorium</label>
          <select class="select" id="laboratory_uuid" name="laboratory_uuid">
            <option value=""><?= activeGroupIs('laboran') ? 'Semua Lab Ditugaskan' : 'Semua Laboratorium' ?></option>
            <?php foreach ($laboratoryOptions as $option): ?>
            <option value="<?= esc($option['uuid']) ?>" <?= $laboratoryUuid === (string) ($option['uuid'] ?? '') ? 'selected' : '' ?>><?= esc($option['name']) ?><?= ! empty($option['room_code']) ? ' (' . esc($option['room_code']) . ')' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:0 0 160px;">
          <label class="text-xs text-muted-foreground" for="status">Status</label>
          <select class="select" id="status" name="status">
            <option value="">Semua Status</option>
            <?php foreach ($statusOptions as $option): ?><option value="<?= esc($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc($statusLabels[$option] ?? ucfirst($option)) ?></option><?php endforeach; ?>
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
          <?php if ($search !== '' || $status !== '' || $laboratoryUuid !== ''): ?>
          <a href="<?= base_url('peminjaman/lab-loans?tab=' . $tab) ?>" class="button button--outline button--sm">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th>Applicant</th><th>Laboratorium</th><th>Event</th><th>Tanggal Pengajuan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php if (! empty($proposals)): ?>
            <?php foreach ($proposals as $proposal): ?>
            <?php $statusLabels = ['draft' => 'Draft', 'submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'cancelled' => 'Dibatalkan', 'completed' => 'Selesai']; $statusColors = ['draft' => 'secondary', 'submitted' => 'warning', 'laboran_approved' => 'info', 'rejected' => 'danger', 'approved' => 'success', 'cancelled' => 'danger', 'completed' => 'primary']; ?>
            <tr>
              <td><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?> &middot; <?= esc($proposal['email']) ?></div></td>
              <td><?= esc($proposal['laboratory_names'] ?? '-') ?></td>
              <td><strong><?= esc($proposal['event_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc(date('d M Y H:i', strtotime($proposal['event_start']))) ?> - <?= esc(date('d M Y H:i', strtotime($proposal['event_end']))) ?></div></td>
              <td><?= esc(date('d M Y', strtotime($proposal['proposal_date']))) ?></td>
              <td><span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? ucfirst($proposal['status'])) ?></span></td>
              <td class="text-end"><div class="flex justify-end gap-1">
                <?php if ($proposal['status'] !== 'draft'): ?><a href="<?= base_url('peminjaman/lab-loans/detail/' . $proposal['uuid']) ?>" class="button button--info button--icon-only button--sm" title="Detail Pengajuan" aria-label="Detail Pengajuan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5a7.5 7.5 0 1 0 0 15a7.5 7.5 0 0 0 0-15Zm0 3.25v.5m0 2.5v4.5" /></svg></a><?php endif; ?>
                <?php if ($proposal['status'] === 'draft' && activeGroupCan('loans.edit')): ?><a href="<?= base_url('peminjaman/lab-loans/edit/' . $proposal['uuid']) ?>" class="button button--warning button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" /></svg></a><?php endif; ?>
                <?php if ($proposal['status'] === 'draft' && activeGroupCan('loans.edit')): ?><a href="<?= base_url('peminjaman/lab-loans/items/' . $proposal['uuid']) ?>" class="button button--primary button--icon-only button--sm" title="Tambah Item Ruangan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg></a><?php endif; ?>
                <?php if ($proposal['status'] === 'draft' && activeGroupCan('loans.delete')): ?><button type="button" class="button button--danger button--icon-only button--sm" title="Batalkan" aria-label="Batalkan pengajuan <?= esc($proposal['event_name'], 'attr') ?>" onclick="openDeleteDialog('<?= esc(base_url('peminjaman/lab-loans/delete/' . $proposal['uuid']), 'js') ?>', '<?= esc($proposal['event_name'], 'js') ?>')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button><?php endif; ?>
                <?php if ($proposal['status'] === 'approved' && activeGroupCan('loans.complete')): ?><button type="button" class="button button--success button--icon-only button--sm" title="Tandai Selesai" aria-label="Tandai Selesai" onclick="openCompleteDialog('<?= esc(base_url('peminjaman/lab-loans/complete/' . $proposal['uuid']), 'js') ?>', '<?= esc($proposal['event_name'], 'js') ?>')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m5 12 4 4L19 6" /></svg></button><?php endif; ?>
                <?php if ($proposal['status'] === 'approved' && activeGroupIs('laboran')): ?><button type="button" class="button button--danger button--icon-only button--sm" title="Batalkan karena kebutuhan mendesak" aria-label="Batalkan pengajuan <?= esc($proposal['event_name'], 'attr') ?>" onclick="openCancelDialog('<?= esc(base_url('peminjaman/lab-loans/cancel/' . $proposal['uuid']), 'js') ?>', '<?= esc($proposal['event_name'], 'js') ?>')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12m0-12L6 18" /></svg></button><?php endif; ?>
              </div></td>
            </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="5" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => ($search !== '' || $status !== '') ? 'Data tidak ditemukan untuk filter tersebut.' : 'Belum ada pengajuan peminjaman.']) ?></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalRows > 0): ?><div class="card__body" style="border-top:1px solid var(--color-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;"><span class="text-xs text-muted-foreground">Total <?= $totalRows ?> pengajuan</span><?= $pager->only(['tab', 'q', 'status', 'perPage'])->links('default', 'app') ?></div><?php endif; ?>
  </div>
</div>

<div class="dialog dialog--sm" id="cancelConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="cancelConfirmLabel" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
      </button>
      <form id="cancelForm" method="post">
        <?= csrf_field() ?>
        <div class="dialog__body">
          <h3 class="dialog__title mb-1" id="cancelConfirmLabel">Batalkan pengajuan?</h3>
          <p class="text-muted-foreground mb-4">Pengajuan <strong data-slot="cancel-event"></strong> akan dibatalkan dan pemohon akan menerima pemberitahuan.</p>
          <div class="field"><label class="field__label" for="cancel_note">Alasan Pembatalan <span class="text-danger">*</span></label><textarea class="input" id="cancel_note" name="note" rows="3" required></textarea></div>
        </div>
        <div class="dialog__footer"><button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button><button type="submit" class="button button--danger">Batalkan Pengajuan</button></div>
      </form>
    </div>
  </div>
</div>

<div class="dialog dialog--sm" id="completeConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="completeConfirmLabel" aria-describedby="completeConfirmDesc" aria-hidden="true" tabindex="-1">
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
        <h3 class="dialog__title mb-1" id="completeConfirmLabel">Tandai pengajuan selesai?</h3>
        <p class="text-muted-foreground" id="completeConfirmDesc">Pengajuan <strong data-slot="complete-event"></strong> akan diubah menjadi status selesai.</p>
      </div>
      <form id="completeForm" method="post">
        <?= csrf_field() ?>
        <div class="dialog__footer justify-center">
          <button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
          <button type="submit" class="button button--success">Ya, Tandai Selesai</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="dialog dialog--sm" id="deleteConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="deleteConfirmLabel" aria-describedby="deleteConfirmDesc" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg>
      </button>
      <div class="dialog__body text-center pt-6">
        <span class="icon-box icon-box--danger icon-box--circle icon-box--lg mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 7h12m-9 0v10m6-10v10M8 7l.75-2h6.5L16 7m-9 0 .75 13h6.5L15 7" /></svg>
        </span>
        <h3 class="dialog__title mb-1" id="deleteConfirmLabel">Batalkan pengajuan?</h3>
        <p class="text-muted-foreground" id="deleteConfirmDesc">Pengajuan <strong data-slot="delete-event"></strong> akan dibatalkan dan dihapus.</p>
      </div>
      <form id="deleteForm" method="post">
        <?= csrf_field() ?>
        <div class="dialog__footer justify-center">
          <button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
          <button type="submit" class="button button--danger">Ya, Batalkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  function openCompleteDialog(actionUrl, eventName) {
    var dialog = document.getElementById('completeConfirm');
    var form = document.getElementById('completeForm');
    if (!dialog || !form) return;
    form.action = actionUrl;
    dialog.querySelector('[data-slot="complete-event"]').textContent = eventName;
    dialog.dataset.state = 'open';
    dialog.setAttribute('aria-hidden', 'false');
    window.requestAnimationFrame(function () {
      var confirmButton = form.querySelector('button[type="submit"]');
      if (confirmButton) confirmButton.focus();
    });
  }

  function openCancelDialog(actionUrl, eventName) {
    var dialog = document.getElementById('cancelConfirm');
    var form = document.getElementById('cancelForm');
    if (!dialog || !form) return;
    form.action = actionUrl;
    form.querySelector('#cancel_note').value = '';
    dialog.querySelector('[data-slot="cancel-event"]').textContent = eventName;
    dialog.dataset.state = 'open';
    dialog.setAttribute('aria-hidden', 'false');
    window.requestAnimationFrame(function () { form.querySelector('#cancel_note').focus(); });
  }

  function openDeleteDialog(actionUrl, eventName) {
    var dialog = document.getElementById('deleteConfirm');
    var form = document.getElementById('deleteForm');
    if (!dialog || !form) return;
    form.action = actionUrl;
    dialog.querySelector('[data-slot="delete-event"]').textContent = eventName;
    dialog.dataset.state = 'open';
    dialog.setAttribute('aria-hidden', 'false');
    window.requestAnimationFrame(function () {
      var cancelButton = dialog.querySelector('[data-stisla-dialog-dismiss]');
      if (cancelButton) cancelButton.focus();
    });
  }

  document.addEventListener('click', function (event) {
    if (!event.target.closest('[data-stisla-dialog-dismiss]')) return;
    var dialog = event.target.closest('[data-stisla-dialog]');
    if (!dialog) return;
    dialog.dataset.state = 'closed';
    dialog.setAttribute('aria-hidden', 'true');
  });

  document.getElementById('completeForm')?.addEventListener('submit', function () {
    var submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) submitButton.disabled = true;
  });

  document.getElementById('deleteForm')?.addEventListener('submit', function () {
    var submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) submitButton.disabled = true;
  });

  document.getElementById('cancelForm')?.addEventListener('submit', function () {
    var submitButton = this.querySelector('button[type="submit"]');
    if (submitButton) submitButton.disabled = true;
  });
</script>