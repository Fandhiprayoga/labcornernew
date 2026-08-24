<?php
/** @var array $asset */
/** @var array $laboratories */
/** @var array $statuses */
?>
<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Edit Asset: <?= esc($asset['name']) ?></span></div>
    <div class="card__body">
      <form action="<?= base_url('admin/assets/update/' . $asset['uuid']) ?>" method="post" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field"><label for="asset_code" class="field__label">Kode Asset <span class="text-danger">*</span></label><input type="text" class="input w-full" id="asset_code" name="asset_code" value="<?= old('asset_code', $asset['asset_code']) ?>" required></div>
          <div class="field"><label for="name" class="field__label">Nama Asset <span class="text-danger">*</span></label><input type="text" class="input w-full" id="name" name="name" value="<?= old('name', $asset['name']) ?>" required></div>
          <div class="field"><label for="laboratory_id" class="field__label">Laboratorium <span class="text-danger">*</span></label><select class="input w-full" id="laboratory_id" name="laboratory_id" required><option value="">Pilih Laboratorium</option><?php foreach ($laboratories as $laboratory): ?><option value="<?= esc($laboratory['id']) ?>" <?= old('laboratory_id', $asset['laboratory_id']) == $laboratory['id'] ? 'selected' : '' ?>><?= esc($laboratory['name'] . ' (' . ($laboratory['room_code'] ?: '-') . ' - ' . ($laboratory['room_name'] ?: '-') . ')') ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="category" class="field__label">Kategori</label><input type="text" class="input w-full" id="category" name="category" value="<?= old('category', $asset['category']) ?>"></div>
          <div class="field"><label for="brand" class="field__label">Merek</label><input type="text" class="input w-full" id="brand" name="brand" value="<?= old('brand', $asset['brand']) ?>"></div>
          <div class="field"><label for="model" class="field__label">Model/Tipe</label><input type="text" class="input w-full" id="model" name="model" value="<?= old('model', $asset['model']) ?>"></div>
          <div class="field"><label for="serial_number" class="field__label">Nomor Seri</label><input type="text" class="input w-full" id="serial_number" name="serial_number" value="<?= old('serial_number', $asset['serial_number']) ?>"></div>
          <div class="field"><label for="acquisition_date" class="field__label">Tanggal Perolehan</label><input type="date" class="input w-full" id="acquisition_date" name="acquisition_date" value="<?= old('acquisition_date', $asset['acquisition_date']) ?>"></div>
          <div class="field"><label for="purchase_price" class="field__label">Harga Perolehan</label><input type="number" min="0" step="0.01" class="input w-full" id="purchase_price" name="purchase_price" value="<?= old('purchase_price', $asset['purchase_price']) ?>"></div>
          <div class="field"><label for="can_be_borrowed" class="field__label">Boleh Dipinjam <span class="text-danger">*</span></label><select class="input w-full" id="can_be_borrowed" name="can_be_borrowed" required><option value="1" <?= old('can_be_borrowed', (string) $asset['can_be_borrowed']) === '1' ? 'selected' : '' ?>>Ya</option><option value="0" <?= old('can_be_borrowed', (string) $asset['can_be_borrowed']) === '0' ? 'selected' : '' ?>>Tidak</option></select></div>
          <div class="field"><label for="status" class="field__label">Status Asset <span class="text-danger">*</span></label><select class="input w-full" id="status" name="status" required><?php foreach ($statuses as $key => $label): ?><option value="<?= esc($key) ?>" <?= old('status', $asset['status']) === $key ? 'selected' : '' ?>><?= esc($label) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="field"><label for="description" class="field__label">Keterangan</label><textarea class="input w-full" id="description" name="description" rows="3" maxlength="500"><?= old('description', $asset['description']) ?></textarea></div>
        <div class="flex justify-end gap-2 pt-2"><a href="<?= base_url('admin/assets') ?>" class="button button--outline button--neutral">Batal</a><button type="submit" class="button button--primary">Perbarui</button></div>
      </form>
    </div>
  </div>
</div>