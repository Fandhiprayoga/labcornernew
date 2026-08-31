<?php
/** @var array $previewRows */
/** @var int $validCount */
/** @var int $invalidCount */
?>
<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Pratinjau Bulk Insert Asset</span></div>
    <div class="card__body flex flex-col gap-4">
      <div class="alert <?= $invalidCount > 0 ? 'alert--warning' : 'alert--success' ?>">
        <div class="alert__body">
          <?= $validCount ?> baris valid siap disimpan<?= $invalidCount > 0 ? ", {$invalidCount} baris ditolak (lihat keterangan di tabel)." : '.' ?>
          Data belum tersimpan, periksa kembali sebelum konfirmasi.
        </div>
      </div>

      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th class="text-center" style="width: 60px;">Baris</th>
              <th class="text-center" style="width: 90px;">Status</th>
              <th>Kode Asset</th>
              <th>Nama Asset</th>
              <th>Laboratorium (ID)</th>
              <th>Status Asset</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($previewRows as $row): ?>
            <tr<?= $row['valid'] ? '' : ' class="table-danger"' ?>>
              <td class="text-center"><?= esc($row['row_number']) ?></td>
              <td class="text-center">
                <?php if ($row['valid']): ?>
                <span class="badge badge--success">Valid</span>
                <?php else: ?>
                <span class="badge badge--danger">Ditolak</span>
                <?php endif; ?>
              </td>
              <td><?= esc($row['raw']['asset_code'] ?? '') ?></td>
              <td><?= esc($row['raw']['name'] ?? '') ?></td>
              <td><?= esc($row['raw']['laboratory_id'] ?? '') ?></td>
              <td><?= esc($row['raw']['status'] ?? '') ?></td>
              <td>
                <?php if (! empty($row['errors'])): ?>
                <ul class="mb-0" style="padding-left: 1rem;">
                  <?php foreach ($row['errors'] as $error): ?>
                  <li class="text-danger"><?= esc($error) ?></li>
                  <?php endforeach; ?>
                </ul>
                <?php else: ?>
                &mdash;
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <form action="<?= base_url('admin/assets/bulk-cancel') ?>" method="post">
          <?= csrf_field() ?>
          <button type="submit" class="button button--outline button--neutral">Batal</button>
        </form>
        <form action="<?= base_url('admin/assets/bulk-confirm') ?>" method="post">
          <?= csrf_field() ?>
          <button type="submit" class="button button--primary" <?= $validCount === 0 ? 'disabled' : '' ?>>Konfirmasi &amp; Simpan (<?= $validCount ?>)</button>
        </form>
      </div>
    </div>
  </div>
</div>
