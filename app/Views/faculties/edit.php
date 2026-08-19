<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Edit Fakultas: <?= esc($faculty['name']) ?></span></div>
    <div class="card__body">
      <form action="<?= base_url('admin/faculties/update/' . $faculty['uuid']) ?>" method="post" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field"><label for="code" class="field__label">Kode Fakultas <span class="text-danger">*</span></label><input type="text" class="input w-full" id="code" name="code" value="<?= old('code', $faculty['code']) ?>" required></div>
          <div class="field"><label for="name" class="field__label">Nama Fakultas <span class="text-danger">*</span></label><input type="text" class="input w-full" id="name" name="name" value="<?= old('name', $faculty['name']) ?>" required></div>
          <div class="field"><label for="dean_name" class="field__label">Nama Dekan</label><input type="text" class="input w-full" id="dean_name" name="dean_name" value="<?= old('dean_name', $faculty['dean_name']) ?>"></div>
          <div class="field"><label for="status" class="field__label">Status <span class="text-danger">*</span></label><select class="input w-full" id="status" name="status" required><option value="active" <?= old('status', $faculty['status']) === 'active' ? 'selected' : '' ?>>Aktif</option><option value="inactive" <?= old('status', $faculty['status']) === 'inactive' ? 'selected' : '' ?>>Nonaktif</option></select></div>
        </div>
        <div class="field"><label for="description" class="field__label">Keterangan</label><textarea class="input w-full" id="description" name="description" rows="3" maxlength="500"><?= old('description', $faculty['description']) ?></textarea></div>
        <div class="flex justify-end gap-2 pt-2"><a href="<?= base_url('admin/faculties') ?>" class="button button--outline button--neutral">Batal</a><button type="submit" class="button button--primary">Perbarui</button></div>
      </form>
    </div>
  </div>
</div>