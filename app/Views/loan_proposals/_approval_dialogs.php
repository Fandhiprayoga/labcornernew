<div class="dialog dialog--sm" id="approveConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel"><div class="dialog__content">
    <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
    <div class="dialog__body text-center pt-6"><h3 class="dialog__title mb-1">Setujui pengajuan ini?</h3><p class="text-muted-foreground">Pengajuan <strong data-slot="approve-event"></strong> akan lanjut ke tahap persetujuan berikutnya.</p></div>
    <form id="approveForm" method="post"><?= csrf_field() ?><div class="dialog__footer justify-center"><button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button><button type="submit" class="button button--success">Ya, Setujui</button></div></form>
  </div></div>
</div>
<div class="dialog dialog--sm" id="rejectConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel"><div class="dialog__content">
    <div class="dialog__header"><h3 class="dialog__title">Tolak Pengajuan</h3><button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button></div>
    <form id="rejectForm" method="post"><?= csrf_field() ?><div class="dialog__body"><p class="text-muted-foreground text-sm mb-4">Alasan penolakan akan terlihat oleh pemohon dan tahap approval sebelumnya.</p><div class="field"><label class="field__label" for="reject_note">Alasan Penolakan <span class="text-danger">*</span></label><textarea class="input" id="reject_note" name="note" rows="3" required></textarea></div></div><div class="dialog__footer"><button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button><button type="submit" class="button button--danger">Tolak Pengajuan</button></div></form>
  </div></div>
</div>
<script>
  function openApprovalDialog(dialogId, actionUrl, eventName) {
    var dialog = document.getElementById(dialogId);
    if (!dialog) return;
    var form = dialog.querySelector('form');
    form.action = actionUrl;
    var slot = dialog.querySelector('[data-slot="' + (dialogId === 'approveConfirm' ? 'approve-event' : 'reject-event') + '"]');
    if (slot) slot.textContent = eventName;
    var note = form.querySelector('#reject_note');
    if (note) note.value = '';
    dialog.dataset.state = 'open';
    dialog.setAttribute('aria-hidden', 'false');
  }
  document.addEventListener('click', function (event) {
    var dismiss = event.target.closest('[data-stisla-dialog-dismiss]');
    var dialog = dismiss ? event.target.closest('[data-stisla-dialog]') : null;
    if (dialog) { dialog.dataset.state = 'closed'; dialog.setAttribute('aria-hidden', 'true'); }
  });
</script>
