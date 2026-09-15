<div class="dialog dialog--sm" id="approveConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="approveConfirmLabel" aria-describedby="approveConfirmDesc" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel"><div class="dialog__content">
    <button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
    <div class="dialog__body text-center pt-6"><h3 class="dialog__title mb-1" id="approveConfirmLabel">Setujui proposal ini?</h3><p class="text-muted-foreground" id="approveConfirmDesc">Proposal <strong data-slot="approve-event"></strong> akan diproses ke tahap berikutnya.</p></div>
    <form id="approveForm" method="post"><?= csrf_field() ?><div class="dialog__footer justify-center"><button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button><button type="submit" class="button button--success">Ya, Setujui</button></div></form>
  </div></div>
</div>
<div class="dialog dialog--sm" id="rejectConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="rejectConfirmLabel" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
  <div class="dialog__panel"><div class="dialog__content"><div class="dialog__header"><h3 class="dialog__title" id="rejectConfirmLabel">Tolak Proposal</h3><button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button></div>
    <form id="rejectForm" method="post"><?= csrf_field() ?><div class="dialog__body"><p class="text-muted-foreground text-sm mb-4">Alasan penolakan akan terlihat oleh pemohon.</p><div class="field"><label class="field__label" for="reject_note">Alasan Penolakan <span class="text-danger">*</span></label><textarea class="input" id="reject_note" name="note" rows="3" required></textarea></div></div><div class="dialog__footer"><button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button><button type="submit" class="button button--danger">Tolak Proposal</button></div></form>
  </div></div>
</div>
<script>
  function openApprovalDialog(dialogId, actionUrl, eventName) {
    var dialog = document.getElementById(dialogId);
    var form = dialog.querySelector('form');
    form.action = actionUrl;
    var slot = dialog.querySelector('[data-slot]');
    if (slot) slot.textContent = eventName;
    if (dialogId === 'rejectConfirm') dialog.querySelector('#reject_note').value = '';
    dialog.dataset.state = 'open';
    dialog.setAttribute('aria-hidden', 'false');
  }
  document.addEventListener('click', function (event) {
    var dismiss = event.target.closest('[data-stisla-dialog-dismiss]');
    if (! dismiss) return;
    var dialog = dismiss.closest('[data-stisla-dialog]');
    if (dialog) { dialog.dataset.state = 'closed'; dialog.setAttribute('aria-hidden', 'true'); }
  });
  document.querySelectorAll('#approveForm, #rejectForm').forEach(function (form) {
    form.addEventListener('submit', function () { var button = form.querySelector('button[type="submit"]'); if (button) button.disabled = true; });
  });
</script>