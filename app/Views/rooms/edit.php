<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Edit Ruangan: <?= esc($room['name']) ?></span></div>
    <div class="card__body">
      <form action="<?= base_url('admin/rooms/update/' . $room['id']) ?>" method="post" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field"><label for="code" class="field__label">Kode Ruangan <span class="text-danger">*</span></label><input type="text" class="input w-full" id="code" name="code" value="<?= old('code', $room['code']) ?>" required></div>
          <div class="field"><label for="name" class="field__label">Nama Ruangan <span class="text-danger">*</span></label><input type="text" class="input w-full" id="name" name="name" value="<?= old('name', $room['name']) ?>" required></div>
          <div class="field"><label for="building" class="field__label">Gedung</label><input type="text" class="input w-full" id="building" name="building" value="<?= old('building', $room['building']) ?>"></div>
          <div class="field"><label for="floor" class="field__label">Lantai</label><input type="number" min="0" max="100" class="input w-full" id="floor" name="floor" value="<?= old('floor', $room['floor']) ?>"></div>
          <div class="field"><label for="capacity" class="field__label">Kapasitas (orang) <span class="text-danger">*</span></label><input type="number" min="1" max="10000" class="input w-full" id="capacity" name="capacity" value="<?= old('capacity', $room['capacity']) ?>" required></div>
          <div class="field"><label for="type" class="field__label">Jenis Ruangan <span class="text-danger">*</span></label><select class="input w-full" id="type" name="type" required><?php foreach (['laboratorium' => 'Laboratorium', 'kelas' => 'Kelas', 'meeting' => 'Meeting', 'gudang' => 'Gudang', 'lainnya' => 'Lainnya'] as $key => $label): ?><option value="<?= $key ?>" <?= old('type', $room['type']) === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="field"><label for="description" class="field__label">Keterangan</label><textarea class="input w-full" id="description" name="description" rows="3" maxlength="500"><?= old('description', $room['description']) ?></textarea></div>
        <div class="field"><label for="status" class="field__label">Status <span class="text-danger">*</span></label><select class="input w-full" id="status" name="status" required><option value="active" <?= old('status', $room['status']) === 'active' ? 'selected' : '' ?>>Aktif</option><option value="inactive" <?= old('status', $room['status']) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option></select></div>
        <div class="flex justify-end gap-2 pt-2"><a href="<?= base_url('admin/rooms') ?>" class="button button--outline button--neutral">Batal</a><button type="submit" class="button button--primary">Perbarui</button></div>
      </form>
    </div>
  </div>
</div>