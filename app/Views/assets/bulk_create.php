<?php
/** @var array $laboratories */
/** @var array $statuses */
?>
<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Bulk Insert Asset</span></div>
    <div class="card__body flex flex-col gap-4">
      <div class="alert alert--info">
        <div class="alert__body">
          <p class="mb-2">Unggah file CSV berisi data asset. Kolom wajib: <code>asset_code, name, laboratory_id, can_be_borrowed, status</code>. Kolom opsional: <code>category, brand, model, serial_number, acquisition_date, purchase_price, description</code>.</p>
          <?php if (activeGroupIs('laboran')): ?>
          <p class="mb-0">Sebagai Laboran, Anda hanya dapat mengimpor asset ke laboratorium yang sudah ditugaskan kepada Anda. Gunakan tabel referensi <code>laboratory_id</code> di bawah ini saat mengisi file CSV.</p>
          <?php endif; ?>
        </div>
      </div>

      <?php if (activeGroupIs('laboran')): ?>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th style="width: 120px;">laboratory_id</th>
              <th>Nama Laboratorium</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($laboratories)): ?>
            <tr><td colspan="2">Belum ada laboratorium yang ditugaskan kepada Anda.</td></tr>
            <?php else: ?>
            <?php foreach ($laboratories as $laboratory): ?>
            <tr>
              <td><strong><?= esc($laboratory['id']) ?></strong></td>
              <td><?= esc($laboratory['name']) ?><?= $laboratory['room_code'] ? ' (' . esc($laboratory['room_code']) . ')' : '' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <div>
        <a href="<?= base_url('admin/assets/bulk-template') ?>" class="button button--outline button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v12m0 0 4-4m-4 4-4-4m-5 7v2a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1v-2" /></svg>
          Download Template CSV
        </a>
      </div>

      <form action="<?= base_url('admin/assets/bulk-store') ?>" method="post" enctype="multipart/form-data" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="field">
          <label for="file" class="field__label">File CSV <span class="text-danger">*</span></label>
          <input type="file" class="input w-full" id="file" name="file" accept=".csv,text/csv" required>
          <div class="text-xs text-muted-foreground">Maksimal 2 MB.</div>
        </div>
        <div class="flex justify-end gap-2 pt-2">
          <a href="<?= base_url('admin/assets') ?>" class="button button--outline button--neutral">Batal</a>
          <button type="submit" class="button button--primary">Impor</button>
        </div>
      </form>
    </div>
  </div>
</div>
