<?php
/** @var array $faculties */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var string $search */
/** @var string $status */
/** @var int $perPage */
/** @var array $perPageOptions */
/** @var int $currentPage */
/** @var int $totalRows */
?>
<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Master Fakultas</span>
      <div class="card__action">
        <?php if (activeGroupCan('faculties.create')): ?>
        <a href="<?= base_url('admin/faculties/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Tambah Fakultas
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body" style="border-bottom: 1px solid var(--color-border);">
      <form method="get" action="<?= base_url('admin/faculties') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 260px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Kode, nama fakultas, atau nama dekan...">
        </div>
        <div style="flex:0 1 190px;min-width:170px;">
          <label class="text-xs text-muted-foreground" for="status">Status</label>
          <select class="select" id="status" name="status">
            <option value="">Semua Status</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>
        <div style="flex:0 0 110px;">
          <label class="text-xs text-muted-foreground" for="perPage">Per Halaman</label>
          <select class="select" id="perPage" name="perPage">
            <?php foreach ($perPageOptions as $option): ?>
            <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:flex;gap:.5rem;">
          <button type="submit" class="button button--primary button--sm">Filter</button>
          <?php if ($search !== '' || $status !== ''): ?>
          <a href="<?= base_url('admin/faculties') ?>" class="button button--outline button--sm">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th class="text-center" style="width: 60px;">#</th><th>Kode</th><th>Nama Fakultas</th><th>Dekan</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (! empty($faculties)): ?>
              <?php $no = (($currentPage - 1) * $perPage) + 1; foreach ($faculties as $faculty): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= esc($faculty['code']) ?></strong></td>
                <td><?= esc($faculty['name']) ?><?php if (! empty($faculty['description'])): ?><div class="text-xs text-muted-foreground"><?= esc($faculty['description']) ?></div><?php endif; ?></td>
                <td><?= esc($faculty['dean_name'] ?: '-') ?></td>
                <td><span class="badge badge--soft badge--<?= $faculty['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $faculty['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="text-center"><div class="flex justify-center gap-1">
                  <?php if (activeGroupCan('faculties.edit')): ?><a href="<?= base_url('admin/faculties/edit/' . $faculty['uuid']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                  <?php if (activeGroupCan('faculties.delete')): ?><form action="<?= base_url('admin/faculties/delete/' . $faculty['uuid']) ?>" method="post" data-faculty-delete-form><?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?>
                </div></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => ($search !== '' || $status !== '') ? 'Data tidak ditemukan untuk filter tersebut.' : 'Belum ada data fakultas.']) ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalRows > 0): ?>
    <div class="card__body" style="border-top: 1px solid var(--color-border); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem;">
      <div class="text-xs text-muted-foreground">
        Menampilkan <?= $faculties ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($faculties) ?> dari <?= $totalRows ?> data
      </div>
      <?= $pager->only(['q', 'status', 'perPage'])->links('default', 'app') ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="dialog dialog--sm" id="facultyDeleteConfirm" data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="facultyDeleteConfirmLabel" aria-describedby="facultyDeleteConfirmDesc" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-faculty-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <button type="button" class="dialog__close" data-faculty-dialog-dismiss aria-label="Tutup"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg></button>
      <div class="dialog__body text-center pt-6">
        <span class="icon-box icon-box--danger icon-box--circle icon-box--lg mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 7h12m-9 0v10m6-10v10M8 7l.75-2h6.5L16 7m-9 0 .75 13h6.5L15 7" /></svg></span>
        <h3 class="dialog__title mb-1" id="facultyDeleteConfirmLabel">Hapus fakultas?</h3>
        <p class="text-muted-foreground" id="facultyDeleteConfirmDesc">Fakultas <strong id="facultyDeleteConfirmName"></strong> akan dihapus.</p>
      </div>
      <form id="facultyDeleteForm" method="post">
        <?= csrf_field() ?>
        <div class="dialog__footer justify-center">
          <button type="button" class="button button--outline button--neutral" data-faculty-dialog-dismiss>Batal</button>
          <button type="submit" class="button button--danger">Ya, Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var dialog = document.getElementById('facultyDeleteConfirm');
    var modalForm = document.getElementById('facultyDeleteForm');
    var name = document.getElementById('facultyDeleteConfirmName');

    document.querySelectorAll('[data-faculty-delete-form]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        modalForm.action = form.action;
        name.textContent = form.closest('tr')?.querySelector('td:nth-child(3)')?.firstChild?.textContent.trim() || 'ini';
        dialog.dataset.state = 'open';
        dialog.setAttribute('aria-hidden', 'false');
        window.requestAnimationFrame(function () { modalForm.querySelector('[data-faculty-dialog-dismiss]').focus(); });
      });
    });

    dialog.querySelectorAll('[data-faculty-dialog-dismiss]').forEach(function (element) {
      element.addEventListener('click', function () {
        dialog.dataset.state = 'closed';
        dialog.setAttribute('aria-hidden', 'true');
      });
    });

    modalForm.addEventListener('submit', function () {
      var submitButton = this.querySelector('button[type="submit"]');
      if (submitButton) submitButton.disabled = true;
    });
  });
</script>