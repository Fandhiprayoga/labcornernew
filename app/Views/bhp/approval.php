<?php
/** @var array $requests */
/** @var object $pager */
/** @var string $search */
/** @var string $status */
/** @var int $perPage */
/** @var array $perPageOptions */
/** @var int $currentPage */
/** @var int $totalRows */

$statusLabels = [
	'PENDING_REVIEW' => 'Menunggu Review',
	'EVIDEN_SUBMITTED' => 'Eviden Dikirim',
];
$statusColors = [
	'PENDING_REVIEW' => 'warning',
	'EVIDEN_SUBMITTED' => 'info',
];
?>
<div class="page__section">
	<div class="card">
		<div class="card__header">
			<span class="card__title">Review Pengajuan BHP</span>
		</div>
		<div class="card__body" style="border-bottom: 1px solid var(--color-border);">
			<form method="get" action="<?= base_url('bhp-approval') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
				<div style="flex:1 1 260px;min-width:220px;">
					<label class="text-xs text-muted-foreground" for="q">Cari</label>
					<input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Kode pengajuan atau program studi...">
				</div>
				<div style="flex:0 1 190px;min-width:170px;">
					<label class="text-xs text-muted-foreground" for="status">Status</label>
					<select class="select" id="status" name="status">
						<option value="">Semua status review</option>
						<?php foreach ($statusLabels as $value => $label): ?>
						<option value="<?= esc($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="flex:0 0 110px;">
					<label class="text-xs text-muted-foreground" for="perPage">Per Halaman</label>
					<select class="select" id="perPage" name="perPage">
						<?php foreach ($perPageOptions as $option): ?>
						<option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div style="display:flex;gap:.5rem;">
					<button class="button button--primary button--sm" type="submit">Filter</button>
					<?php if ($search !== '' || $status !== ''): ?>
					<a href="<?= base_url('bhp-approval') ?>" class="button button--outline button--sm">Reset</a>
					<?php endif; ?>
				</div>
			</form>
		</div>
		<div class="card__body p-0">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th>Kode Pengajuan</th>
							<th>Program Studi / Laboratorium</th>
							<th>Status Review</th>
							<th class="text-end">Aksi</th>
						</tr>
					</thead>
					<tbody>
						<?php if (! empty($requests)): ?>
							<?php foreach ($requests as $row): ?>
							<tr>
								<td><strong><?= esc($row['kode_pengajuan']) ?></strong></td>
								<td><?= esc($row['study_program_name'] ?: $row['prodi_snapshot'] ?: '-') ?> / <?= esc($row['laboratory_name'] ?: '-') ?></td>
								<td><span class="badge badge--soft badge--<?= esc($statusColors[$row['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$row['status']] ?? $row['status']) ?></span></td>
								<td class="text-end">
									<div class="flex justify-end gap-1">
										<a class="button button--info button--icon-only button--sm" href="<?= base_url('bhp/detail/' . $row['uuid']) ?>" title="Detail" aria-label="Lihat detail pengajuan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" fill="none" stroke="currentColor" stroke-width="1.5" /></svg></a>
										<?php if ($row['status'] === 'PENDING_REVIEW'): ?>
										<form method="post" action="<?= base_url('bhp-approval/' . $row['uuid'] . '/approve') ?>" style="display:inline"><?= csrf_field() ?><button class="button button--success button--icon-only button--sm" title="Setujui" aria-label="Setujui pengajuan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5 12 4 4L19 6" /></svg></button></form>
										<button type="button" class="button button--warning button--icon-only button--sm" title="Minta revisi" aria-label="Minta revisi pengajuan" onclick="openBhpApprovalRemarkDialog('bhpReviseConfirm', '<?= esc(base_url('bhp-approval/' . $row['uuid'] . '/revise'), 'js') ?>', '<?= esc($row['kode_pengajuan'], 'js') ?>')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" /></svg></button>
										<button type="button" class="button button--danger button--icon-only button--sm" title="Tolak" aria-label="Tolak pengajuan" onclick="openBhpApprovalRemarkDialog('bhpRejectConfirm', '<?= esc(base_url('bhp-approval/' . $row['uuid'] . '/reject'), 'js') ?>', '<?= esc($row['kode_pengajuan'], 'js') ?>')"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6l12 12M18 6 6 18" /></svg></button>
										<?php endif; ?>
									</div>
								</td>
							</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr><td colspan="4" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => 'Tidak ada pengajuan yang menunggu tindakan.']) ?></td></tr>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php if ($totalRows > 0): ?>
		<div class="card__body" style="border-top: 1px solid var(--color-border); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem;">
			<div class="text-xs text-muted-foreground">
				Menampilkan <?= $requests ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($requests) ?> dari <?= $totalRows ?> data
			</div>
			<?= $pager->only(['q', 'status', 'perPage'])->links('default', 'app') ?>
		</div>
		<?php endif; ?>
	</div>
</div>

<div class="dialog dialog--sm" id="bhpReviseConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="bhpReviseConfirmLabel" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<div class="dialog__header">
				<h3 class="dialog__title" id="bhpReviseConfirmLabel">Minta Revisi Pengajuan</h3>
				<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			</div>
			<form id="bhpReviseForm" method="post">
				<?= csrf_field() ?>
				<div class="dialog__body">
					<p class="text-muted-foreground text-sm mb-4">Catatan revisi untuk pengajuan <strong data-slot="bhp-revise-code"></strong> akan terlihat oleh pemohon.</p>
					<div class="field">
						<label class="field__label" for="bhp_revise_note">Remark Revisi <span class="text-danger">*</span></label>
						<textarea class="input" id="bhp_revise_note" name="note" rows="3" required placeholder="Tuliskan catatan revisi..."></textarea>
					</div>
				</div>
				<div class="dialog__footer">
					<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
					<button type="submit" class="button button--warning">Kirim Revisi</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="dialog dialog--sm" id="bhpRejectConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="bhpRejectConfirmLabel" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<div class="dialog__header">
				<h3 class="dialog__title" id="bhpRejectConfirmLabel">Tolak Pengajuan</h3>
				<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			</div>
			<form id="bhpRejectForm" method="post">
				<?= csrf_field() ?>
				<div class="dialog__body">
					<p class="text-muted-foreground text-sm mb-4">Alasan penolakan untuk pengajuan <strong data-slot="bhp-reject-code"></strong> akan terlihat oleh pemohon.</p>
					<div class="field">
						<label class="field__label" for="bhp_reject_note">Alasan Penolakan <span class="text-danger">*</span></label>
						<textarea class="input" id="bhp_reject_note" name="note" rows="3" required placeholder="Tuliskan alasan penolakan..."></textarea>
					</div>
				</div>
				<div class="dialog__footer">
					<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
					<button type="submit" class="button button--danger">Tolak Pengajuan</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	function openBhpApprovalRemarkDialog(dialogId, actionUrl, requestCode) {
		var dialog = document.getElementById(dialogId);
		if (! dialog) return;

		var form = dialog.querySelector('form');
		var note = dialog.querySelector('textarea[name="note"]');
		var slot = dialog.querySelector('[data-slot]');

		if (form) form.action = actionUrl;
		if (note) note.value = '';
		if (slot) slot.textContent = requestCode;

		dialog.dataset.state = 'open';
		dialog.setAttribute('aria-hidden', 'false');
		window.requestAnimationFrame(function () {
			if (note) note.focus();
		});
	}

	document.addEventListener('click', function (event) {
		var dismiss = event.target.closest('[data-stisla-dialog-dismiss]');
		if (! dismiss) return;

		var dialog = dismiss.closest('[data-stisla-dialog]');
		if (! dialog) return;

		dialog.dataset.state = 'closed';
		dialog.setAttribute('aria-hidden', 'true');
	});

	document.querySelectorAll('#bhpReviseForm, #bhpRejectForm').forEach(function (form) {
		form.addEventListener('submit', function () {
			var submitButton = form.querySelector('button[type="submit"]');
			if (submitButton) submitButton.disabled = true;
		});
	});
</script>
