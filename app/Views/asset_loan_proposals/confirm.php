<?php
/** @var array $proposal */
/** @var array $cart */

$fmt = static fn (string $value): string => date('d M Y H:i', strtotime($value));
?>
<style>
	.loan-confirm__grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
	.loan-confirm__details { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1rem; }
	.loan-confirm__item { display: flex; gap: .75rem; align-items: flex-start; padding: .875rem 0; border-bottom: 1px solid var(--color-border); }
	.loan-confirm__item:last-child { border-bottom: 0; padding-bottom: 0; }
	.loan-confirm__item:first-child { padding-top: 0; }
	@media (min-width: 40rem) { .loan-confirm__details { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
	@media (min-width: 64rem) { .loan-confirm__grid { grid-template-columns: minmax(0, 1fr) 20rem; } }
</style>
<div class="page__section flex flex-col gap-4">
	<div class="card">
		<div class="card__header">
			<span class="card__title">Konfirmasi Pengajuan Peminjaman</span>
			<div class="card__action">
				<a href="<?= base_url('peminjaman/asset-loans/items/' . $proposal['uuid']) ?>" class="button button--outline button--neutral button--sm">Kembali</a>
			</div>
		</div>
		<div class="card__body">
			<p class="text-sm text-muted-foreground" style="margin:0 0 1rem;">Periksa kembali data pengajuan dan asset yang akan dipinjam sebelum mengajukan.</p>
			<div class="loan-confirm__details">
				<div><div class="text-xs text-muted-foreground">Nama Kegiatan</div><strong><?= esc($proposal['event_name']) ?></strong></div>
				<div><div class="text-xs text-muted-foreground">Tanggal Pengajuan</div><strong><?= esc(date('d M Y', strtotime($proposal['proposal_date']))) ?></strong></div>
				<div><div class="text-xs text-muted-foreground">Pemohon</div><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?></div></div>
				<div><div class="text-xs text-muted-foreground">Kontak</div><strong><?= esc($proposal['phone']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['email']) ?></div></div>
				<div><div class="text-xs text-muted-foreground">Waktu Mulai</div><strong><?= esc($fmt($proposal['event_start'])) ?></strong></div>
				<div><div class="text-xs text-muted-foreground">Waktu Selesai</div><strong><?= esc($fmt($proposal['event_end'])) ?></strong></div>
			</div>
		</div>
	</div>

	<div class="loan-confirm__grid">
		<div class="card">
			<div class="card__header">
				<span class="card__title">Asset yang Dipinjam</span>
				<span class="badge badge--soft badge--primary"><?= count($cart) ?> asset</span>
			</div>
			<div class="card__body">
				<?php foreach ($cart as $item): ?>
				<div class="loan-confirm__item">
					<img src="<?= base_url($item['photo'] ?: 'assets/images/default-asset.svg') ?>" alt="" width="64" height="64" style="object-fit:cover;border-radius:.5rem;flex:0 0 64px;">
					<div style="min-width:0;">
						<strong><?= esc($item['asset_code']) ?></strong>
						<div class="text-sm text-muted-foreground"><?= esc($item['asset_name']) ?></div>
						<div class="text-xs text-muted-foreground"><?= esc($item['laboratory_name'] ?: '-') ?><?php if (! empty($item['room_code'])): ?> &middot; <?= esc($item['room_code']) ?><?php endif; ?></div>
						<?php if (! empty($item['notes'])): ?><div class="text-xs" style="margin-top:.25rem;">Catatan: <?= esc($item['notes']) ?></div><?php endif; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="card">
			<div class="card__body">
				<div style="display:flex;justify-content:center;margin:0 0 1rem;"><svg viewBox="0 0 200 200" role="img" aria-label="Peringatan" xmlns="http://www.w3.org/2000/svg" width="200" height="200"><defs><linearGradient id="asset-loan-confirm-warning-light" x1=".15" y1=".05" x2=".85" y2=".95"><stop offset="0" stop-color="oklch(0.8548 0.0627173 257.676)"/><stop offset="1" stop-color="oklch(0.75231 0.106953 257.676)"/></linearGradient><linearGradient id="asset-loan-confirm-warning" x1=".15" y1=".05" x2=".85" y2=".95"><stop offset="0" stop-color="oklch(0.75231 0.106953 257.676)"/><stop offset="1" stop-color="oklch(0.649821 0.151189 257.676)"/></linearGradient><filter id="asset-loan-confirm-warning-shadow" x="-40%" y="-40%" width="180%" height="180%"><feDropShadow dx="5" dy="9" stdDeviation="5.5" flood-color="oklch(0.572953 0.184366 257.676 / 0.26)"/></filter></defs><circle cx="100" cy="94" r="72" fill="oklch(0.572953 0.184366 257.676 / 0.06)"/><circle cx="100" cy="94" r="55" fill="oklch(0.572953 0.184366 257.676 / 0.1)"/><g filter="url(#asset-loan-confirm-warning-shadow)"><path d="M100 54q5 0 8 5l44 76q4 7-4 7H52q-8 0-4-7l44-76q3-5 8-5z" fill="url(#asset-loan-confirm-warning)"/><rect x="95" y="84" width="10" height="34" rx="5" fill="#fff"/><circle cx="100" cy="129" r="5.5" fill="#fff"/></g></svg></div>
				<div class="text-sm text-muted-foreground" style="margin-bottom:1rem;">Dengan mengajukan peminjaman, data di atas akan dikirim untuk diproses.</div>
				<button type="button" class="button button--primary w-full" onclick="openAssetLoanConfirmDialog()">Ajukan Peminjaman <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 12h14m-6-6 6 6-6 6" /></svg></button>
			</div>
		</div>
	</div>
</div>

<div class="dialog dialog--sm" id="assetLoanSubmitConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="assetLoanSubmitConfirmLabel" aria-describedby="assetLoanSubmitConfirmDesc" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg></button>
			<div class="dialog__body text-center pt-6"><span class="icon-box icon-box--primary icon-box--circle icon-box--lg mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 12h14m-6-6 6 6-6 6" /></svg></span><h3 class="dialog__title mb-1" id="assetLoanSubmitConfirmLabel">Ajukan peminjaman ini?</h3><p class="text-muted-foreground" id="assetLoanSubmitConfirmDesc">Data pengajuan akan dikirim untuk diproses setelah Anda mengonfirmasi.</p></div>
			<form id="assetLoanSubmitForm" action="<?= base_url('peminjaman/asset-loans/submit/' . $proposal['uuid']) ?>" method="post">
				<?= csrf_field() ?>
				<div class="dialog__footer justify-center"><button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button><button type="submit" class="button button--primary">Ya, Ajukan</button></div>
			</form>
		</div>
	</div>
</div>

<script>
	function openAssetLoanConfirmDialog() {
		var dialog = document.getElementById('assetLoanSubmitConfirm');
		if (!dialog) return;
		dialog.dataset.state = 'open';
		dialog.setAttribute('aria-hidden', 'false');
		window.requestAnimationFrame(function () {
			var cancelButton = dialog.querySelector('[data-stisla-dialog-dismiss]');
			if (cancelButton) cancelButton.focus();
		});
	}

	document.addEventListener('click', function (event) {
		if (!event.target.closest('[data-stisla-dialog-dismiss]')) return;
		var dialog = event.target.closest('[data-stisla-dialog]');
		if (!dialog) return;
		dialog.dataset.state = 'closed';
		dialog.setAttribute('aria-hidden', 'true');
	});

	document.querySelector('#assetLoanSubmitForm').addEventListener('submit', function () {
		var submitButton = this.querySelector('button[type="submit"]');
		if (submitButton) submitButton.disabled = true;
	});
</script>
