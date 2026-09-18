<?php
/** @var array $proposal */
/** @var array $items */
$statusLabels = ['draft' => 'Draft', 'submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'completed' => 'Selesai'];
$statusColors = ['draft' => 'secondary', 'submitted' => 'warning', 'laboran_approved' => 'info', 'rejected' => 'danger', 'approved' => 'success', 'completed' => 'primary'];
?>
<style>
  .asset-pickup__photo { width:4rem; height:3rem; object-fit:cover; border-radius:.5rem; display:block; }
</style>
<div class="page__section flex flex-col gap-4">
  <div class="card">
    <div class="card__header">
      <div>
        <span class="card__title">Pengambilan Asset</span>
        <div class="text-xs text-muted-foreground"><?= esc($proposal['event_name']) ?></div>
      </div>
      <div class="card__action">
        <span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? ucfirst($proposal['status'])) ?></span>
        <a href="<?= base_url('peminjaman/asset-loans') ?>" class="button button--outline button--neutral button--sm">Kembali</a>
      </div>
    </div>
    <div class="card__body">
      <form id="assetPickupForm" method="post" action="<?= base_url('peminjaman/asset-loans/pickup/' . $proposal['uuid']) ?>">
        <?= csrf_field() ?>
        <div class="flex flex-col gap-4">
          <div class="text-sm text-muted-foreground">Centang asset yang sudah diambil oleh peminjam. Pengembalian baru tersedia setelah semua asset dicatat sudah diambil.</div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th class="text-center">Diambil</th>
                  <th>Gambar</th>
                  <th>Asset</th>
                  <th>Laboratorium</th>
                  <th>Waktu Pengambilan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                  <tr>
                    <td class="text-center"><input type="checkbox" name="taken[]" value="<?= esc($item['asset_id']) ?>" <?= ! empty($item['is_taken']) ? 'checked' : '' ?>></td>
                    <td><img class="asset-pickup__photo" src="<?= esc(base_url($item['photo'] ?: 'assets/images/default-asset.svg'), 'attr') ?>" alt="Foto <?= esc($item['asset_name'], 'attr') ?>"></td>
                    <td><strong><?= esc($item['asset_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($item['asset_code']) ?> · <?= esc($item['category'] ?: '-') ?></div></td>
                    <td><?= esc($item['laboratory_name'] ?: '-') ?></td>
                    <td><?= ! empty($item['taken_at']) ? esc(date('d M Y H:i', strtotime($item['taken_at']))) : '<span class="text-muted-foreground">Belum diambil</span>' ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" class="button button--primary" id="openAssetPickupConfirm">Simpan Status Pengambilan</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="dialog dialog--sm" id="assetPickupConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="assetPickupConfirmLabel" aria-describedby="assetPickupConfirmDesc" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
      <div class="dialog__body text-center pt-6">
        <span class="icon-box icon-box--success icon-box--circle icon-box--lg mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m5 12 4 4L19 6" /></svg>
        </span>
        <h3 class="dialog__title mb-1" id="assetPickupConfirmLabel">Simpan status pengambilan?</h3>
        <p class="text-muted-foreground" id="assetPickupConfirmDesc">Status checklist asset yang sudah diambil akan disimpan.</p>
      </div>
      <div class="dialog__footer justify-center">
        <button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
        <button type="button" class="button button--primary" id="confirmAssetPickupSubmit">Ya, Simpan</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('assetPickupForm');
    var dialog = document.getElementById('assetPickupConfirm');
    var openButton = document.getElementById('openAssetPickupConfirm');
    var confirmButton = document.getElementById('confirmAssetPickupSubmit');
    if (!form || !dialog || !openButton || !confirmButton) return;

    openButton.addEventListener('click', function () {
      dialog.dataset.state = 'open';
      dialog.setAttribute('aria-hidden', 'false');
      window.requestAnimationFrame(function () { confirmButton.focus(); });
    });

    confirmButton.addEventListener('click', function () {
      confirmButton.disabled = true;
      form.submit();
    });

    document.addEventListener('click', function (event) {
      if (!event.target.closest('[data-stisla-dialog-dismiss]')) return;
      var currentDialog = event.target.closest('[data-stisla-dialog]');
      if (!currentDialog) return;
      currentDialog.dataset.state = 'closed';
      currentDialog.setAttribute('aria-hidden', 'true');
    });
  });
</script>