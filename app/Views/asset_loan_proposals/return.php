<?php
/** @var array $proposal */
/** @var array $items */
$statusLabels = ['draft' => 'Draft', 'submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'completed' => 'Selesai'];
$statusColors = ['draft' => 'secondary', 'submitted' => 'warning', 'laboran_approved' => 'info', 'rejected' => 'danger', 'approved' => 'success', 'completed' => 'primary'];
$fmt = static fn (string $value): string => date('d M Y H:i', strtotime($value));
?>
<style>
  .asset-return__photo { width:4rem; height:3rem; object-fit:cover; border-radius:.5rem; display:block; }
</style>
<div class="page__section flex flex-col gap-4">
  <div class="card">
    <div class="card__header">
      <div>
        <span class="card__title">Pengembalian Asset</span>
        <div class="text-xs text-muted-foreground"><?= esc($proposal['event_name']) ?></div>
      </div>
      <div class="card__action">
        <span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? ucfirst($proposal['status'])) ?></span>
        <a href="<?= base_url('peminjaman/asset-loans') ?>" class="button button--outline button--neutral button--sm">Kembali</a>
      </div>
    </div>
    <div class="card__body">
      <form method="post" action="<?= base_url('peminjaman/asset-loans/returns/' . $proposal['uuid']) ?>">
        <?= csrf_field() ?>
        <div class="flex flex-col gap-4">
          <div class="text-sm text-muted-foreground">Centang asset yang sudah dikembalikan. Setelah semua aset selesai dikembalikan, proposal akan otomatis berubah menjadi selesai.</div>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th class="text-center">Dikembalikan</th>
                  <th>Gambar</th>
                  <th>Asset</th>
                  <th>Laboratorium</th>
                  <th>Catatan</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item): ?>
                  <tr>
                    <td class="text-center"><input type="checkbox" name="returned[]" value="<?= esc($item['asset_id']) ?>" <?= ! empty($item['is_returned']) ? 'checked' : '' ?>></td>
                    <td><img class="asset-return__photo" src="<?= esc(base_url($item['photo'] ?: 'assets/images/default-asset.svg'), 'attr') ?>" alt="Foto <?= esc($item['asset_name'], 'attr') ?>"></td>
                    <td><strong><?= esc($item['asset_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($item['asset_code']) ?> · <?= esc($item['category'] ?: '-') ?></div></td>
                    <td><?= esc($item['laboratory_name'] ?: '-') ?></td>
                    <td><input type="text" class="input" name="return_note_<?= esc($item['asset_id']) ?>" value="<?= esc($item['return_note'] ?? '') ?>" placeholder="Catatan pengembalian"></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="flex justify-end gap-2">
            <button type="submit" class="button button--primary">Simpan Status Pengembalian</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
