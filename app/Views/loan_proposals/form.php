<?php
$isEdit = ! empty($proposal);
$value = static function (string $field, string $default = '') use ($proposal): string {
    return old($field, $proposal[$field] ?? $default);
};
$datetime = static function (string $field) use ($value): string {
    $raw = $value($field);
    return $raw ? date('Y-m-d\TH:i', strtotime($raw)) : '';
};
?>
<div class="page__section">
  <div class="card">
    <div class="card__header"><span class="card__title"><?= $isEdit ? 'Edit' : 'Ajukan' ?> Proposal Peminjaman</span></div>
    <div class="card__body">
      <form action="<?= base_url('peminjaman/lab-loans/' . ($isEdit ? 'update/' . $proposal['uuid'] : 'store')) ?>" method="post" class="flex flex-col gap-6">
        <?= csrf_field() ?>

        <fieldset style="border:1px solid var(--color-border);border-radius:.75rem;padding:1.25rem;margin:0;">
          <legend class="text-sm font-semibold" style="padding:0 .5rem;">Data Pemohon</legend>
          <p class="text-xs text-muted-foreground" style="margin:0 0 1rem;">Identitas dan kontak pihak yang mengajukan peminjaman.</p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="field">
              <label for="identity_number" class="field__label">Nomor Identitas <span class="text-danger">*</span></label>
              <input class="input w-full" id="identity_number" name="identity_number" value="<?= esc($value('identity_number')) ?>" placeholder="NIM / NIP" required>
            </div>
            <div class="field">
              <label for="full_name" class="field__label">Nama Lengkap <span class="text-danger">*</span></label>
              <input class="input w-full" id="full_name" name="full_name" value="<?= esc($value('full_name', $user->username ?? '')) ?>" required>
            </div>
            <div class="field">
              <label for="phone" class="field__label">Nomor HP <span class="text-danger">*</span></label>
              <input type="tel" class="input w-full" id="phone" name="phone" value="<?= esc($value('phone', $user->phone ?? '')) ?>" placeholder="08xxxxxxxxxx" required>
            </div>
            <div class="field">
              <label for="email" class="field__label">Email <span class="text-danger">*</span></label>
              <input type="email" class="input w-full" id="email" name="email" value="<?= esc($value('email', $user->email ?? '')) ?>" required>
            </div>
            <div class="field">
              <label for="proposal_date" class="field__label">Tanggal Proposal <span class="text-danger">*</span></label>
              <input type="date" class="input w-full" id="proposal_date" name="proposal_date" value="<?= esc($value('proposal_date', date('Y-m-d'))) ?>" required>
            </div>
          </div>
        </fieldset>

        <fieldset style="border:1px solid var(--color-border);border-radius:.75rem;padding:1.25rem;margin:0;">
          <legend class="text-sm font-semibold" style="padding:0 .5rem;">Detail Kegiatan</legend>
          <p class="text-xs text-muted-foreground" style="margin:0 0 1rem;">Informasi kegiatan beserta rentang waktu penggunaan laboratorium.</p>
          <div class="flex flex-col gap-4">
            <div class="field">
              <label for="event_name" class="field__label">Nama Kegiatan <span class="text-danger">*</span></label>
              <input class="input w-full" id="event_name" name="event_name" value="<?= esc($value('event_name')) ?>" placeholder="Praktikum Jaringan Komputer" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="field">
                <label for="event_start" class="field__label">Waktu Mulai <span class="text-danger">*</span></label>
                <input type="datetime-local" class="input w-full" id="event_start" name="event_start" value="<?= esc($datetime('event_start')) ?>" required>
              </div>
              <div class="field">
                <label for="event_end" class="field__label">Waktu Selesai <span class="text-danger">*</span></label>
                <input type="datetime-local" class="input w-full" id="event_end" name="event_end" value="<?= esc($datetime('event_end')) ?>" required>
              </div>
            </div>
          </div>
        </fieldset>

        <fieldset style="border:1px solid var(--color-border);border-radius:.75rem;padding:1.25rem;margin:0;">
          <legend class="text-sm font-semibold" style="padding:0 .5rem;">Persetujuan</legend>
          <label class="flex gap-2 items-start text-sm">
            <input type="checkbox" class="checkbox" name="acknowledgement" value="1" <?= old('acknowledgement', $proposal['acknowledgement'] ?? '') === '1' || (int) ($proposal['acknowledgement'] ?? 0) === 1 ? 'checked' : '' ?> required>
            <span>Saya memahami bahwa apabila terdapat kegiatan institusi yang tidak terduga, jadwal penggunaan yang telah disetujui dapat dibatalkan atau ditunda.</span>
          </label>
        </fieldset>

        <div class="flex justify-end gap-2" style="border-top:1px solid var(--color-border);padding-top:1rem;">
          <a href="<?= base_url('peminjaman/lab-loans') ?>" class="button button--outline button--neutral">Batal</a>
          <button type="submit" class="button button--primary">Simpan Proposal</button>
        </div>
      </form>
    </div>
  </div>
</div>