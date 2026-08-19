<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Tambah Fakultas</span></div>
    <div class="card__body">
      <form action="<?= base_url('admin/faculties/store') ?>" method="post" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field"><label for="code" class="field__label">Kode Fakultas <span class="text-danger">*</span></label><input type="text" class="input w-full" id="code" name="code" value="<?= old('code') ?>" placeholder="FTEK" required></div>
          <div class="field"><label for="name" class="field__label">Nama Fakultas <span class="text-danger">*</span></label><input type="text" class="input w-full" id="name" name="name" value="<?= old('name') ?>" placeholder="Fakultas Teknologi" required></div>
          <div class="field"><label for="dean_name" class="field__label">Nama Dekan</label><input type="text" class="input w-full" id="dean_name" name="dean_name" value="<?= old('dean_name') ?>" placeholder="Dr. Nama Dekan"></div>
          <div class="field"><label for="status" class="field__label">Status <span class="text-danger">*</span></label><select class="input w-full" id="status" name="status" required><option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Aktif</option><option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option></select></div>
        </div>
        <div class="field"><label for="description" class="field__label">Keterangan</label><textarea class="input w-full" id="description" name="description" rows="3" maxlength="500"><?= old('description') ?></textarea></div>
        <div class="flex justify-end gap-2 pt-2"><a href="<?= base_url('admin/faculties') ?>" class="button button--outline button--neutral">Batal</a><button type="submit" class="button button--primary">Simpan</button></div>
      </form>
    </div>
  </div>
</div>