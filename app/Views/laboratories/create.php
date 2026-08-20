<div class="page__section">
  <div class="card" style="width: 100%;">
    <div class="card__header"><span class="card__title">Tambah Laboratorium</span></div>
    <div class="card__body">
      <form action="<?= base_url('admin/laboratories/store') ?>" method="post" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="field"><label for="name" class="field__label">Nama Laboratorium <span class="text-danger">*</span></label><input type="text" class="input w-full" id="name" name="name" value="<?= old('name') ?>" placeholder="Programming Laboratory" required></div>
          <div class="field"><label for="room_id" class="field__label">Ruangan <span class="text-danger">*</span></label><select class="input w-full" id="room_id" name="room_id" required><option value="">Pilih Ruangan Laboratorium</option><?php foreach ($rooms as $room): ?><option value="<?= esc($room['id']) ?>" <?= old('room_id') == $room['id'] ? 'selected' : '' ?>><?= esc($room['code'] . ' - ' . $room['name']) ?></option><?php endforeach; ?></select></div>
          <div class="field"><label for="status" class="field__label">Status <span class="text-danger">*</span></label><select class="input w-full" id="status" name="status" required><option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>Aktif</option><option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option></select></div>
        </div>
        <div class="field"><span class="field__label">Program Studi <span class="text-danger">*</span></span><div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-2"><?php foreach ($studyPrograms as $studyProgram): ?><label class="flex items-center gap-2"><input type="checkbox" name="study_program_ids[]" value="<?= esc($studyProgram['id']) ?>" <?= in_array($studyProgram['id'], (array) old('study_program_ids', [])) ? 'checked' : '' ?>><span><?= esc($studyProgram['degree'] . ' ' . $studyProgram['code'] . ' - ' . $studyProgram['name']) ?></span></label><?php endforeach; ?></div></div>
        <div class="field"><label for="description" class="field__label">Keterangan</label><textarea class="input w-full" id="description" name="description" rows="3" maxlength="500"><?= old('description') ?></textarea></div>
        <div class="flex justify-end gap-2 pt-2"><a href="<?= base_url('admin/laboratories') ?>" class="button button--outline button--neutral">Batal</a><button type="submit" class="button button--primary">Simpan</button></div>
      </form>
    </div>
  </div>
</div>