<div class="page__section">
  <div class="card">
    <div class="card__header"><span class="card__title">Edit Penugasan Laboran</span></div>
    <div class="card__body">
      <form action="<?= base_url('admin/laboratory-laborans/update/' . $assignment['id']) ?>" method="post" class="flex flex-col gap-4">
        <?= csrf_field() ?>
        <div class="field"><label for="user_id" class="field__label">Laboran <span class="text-danger">*</span></label><select class="input w-full" id="user_id" name="user_id" required data-searchable-select data-placeholder="Cari laboran..."><option value="">Pilih Laboran</option><?php foreach ($laborans as $laboran): ?><option value="<?= esc($laboran['id']) ?>" <?= old('user_id', $assignment['user_id']) == $laboran['id'] ? 'selected' : '' ?>><?= esc($laboran['username'] . ' - ' . ($laboran['email'] ?: '-')) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="laboratory_id" class="field__label">Laboratorium <span class="text-danger">*</span></label><select class="input w-full" id="laboratory_id" name="laboratory_id" required data-searchable-select data-placeholder="Cari laboratorium..."><option value="">Pilih Laboratorium</option><?php foreach ($laboratories as $laboratory): ?><option value="<?= esc($laboratory['id']) ?>" <?= old('laboratory_id', $assignment['laboratory_id']) == $laboratory['id'] ? 'selected' : '' ?>><?= esc($laboratory['name'] . ' (' . ($laboratory['room_code'] ?: '-') . ' - ' . ($laboratory['room_name'] ?: '-') . ')') ?></option><?php endforeach; ?></select></div>
        <div class="flex justify-end gap-2 pt-2"><a href="<?= base_url('admin/laboratory-laborans') ?>" class="button button--outline button--neutral">Batal</a><button type="submit" class="button button--primary">Perbarui</button></div>
      </form>
    </div>
  </div>
</div>
<?= $this->include('partials/searchable_select_assets') ?>