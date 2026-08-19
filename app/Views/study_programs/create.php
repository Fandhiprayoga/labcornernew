<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Tambah Program Studi</span></div>
    <div class="card__body">
      <form action="<?= base_url('admin/study-programs/store') ?>" method="post" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field"><label for="faculty_id" class="field__label">Fakultas <span class="text-danger">*</span></label><select class="input w-full" id="faculty_id" name="faculty_id" required><option value="">Pilih Fakultas</option><?php foreach ($faculties as $faculty): ?><option value="<?= esc($faculty['id']) ?>" <?= old('faculty_id') == $faculty['id'] ? 'selected' : '' ?>><?= esc($faculty['code'] . ' - ' . $faculty['name']) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="degree" class="field__label">Jenjang <span class="text-danger">*</span></label><select class="input w-full" id="degree" name="degree" required><option value="">Pilih Jenjang</option><?php foreach (['D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3', 'Profesi'] as $degree): ?><option value="<?= $degree ?>" <?= old('degree') === $degree ? 'selected' : '' ?>><?= $degree ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="code" class="field__label">Kode Program Studi <span class="text-danger">*</span></label><input type="text" class="input w-full" id="code" name="code" value="<?= old('code') ?>" placeholder="IF-S1" required></div>
          <div class="field"><label for="name" class="field__label">Nama Program Studi <span class="text-danger">*</span></label><input type="text" class="input w-full" id="name" name="name" value="<?= old('name') ?>" placeholder="Informatika" required></div>
          <div class="field"><label for="status" class="field__label">Status <span class="text-danger">*</span></label><select class="input w-full" id="status" name="status" required><option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Aktif</option><option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option></select></div>
        </div>
        <div class="field"><label for="description" class="field__label">Keterangan</label><textarea class="input w-full" id="description" name="description" rows="3" maxlength="500"><?= old('description') ?></textarea></div>
        <div class="flex justify-end gap-2 pt-2"><a href="<?= base_url('admin/study-programs') ?>" class="button button--outline button--neutral">Batal</a><button type="submit" class="button button--primary">Simpan</button></div>
      </form>
    </div>
  </div>
</div>