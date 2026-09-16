<?php
$statusLabels = ['draft' => 'Draft', 'submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'completed' => 'Selesai'];
$statusColors = ['draft' => 'secondary', 'submitted' => 'warning', 'laboran_approved' => 'info', 'rejected' => 'danger', 'approved' => 'success', 'completed' => 'primary'];
$canApprove = activeGroupCan('loans.approve');
?>
<style>
	.page__section>.card>.card__body:last-child {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
	}

	.page__section .table td.text-center.text-muted-foreground.py-8::before {
		content: "";
		display: block;
		width: 120px;
		height: 120px;
		margin: 0 auto .5rem;
		background: url("<?= base_url('assets/img/no-results.svg') ?>") center/contain no-repeat;
	}
</style>
<div class="page__section">
	<div class="card">
		<div class="card__header"><span class="card__title">Proposal Peminjaman Asset</span>
			<div class="card__action"><?php if (activeGroupCan('loans.create')): ?><a href="<?= base_url('peminjaman/asset-loans/create') ?>" class="button button--primary button--sm">+ Ajukan Proposal</a><?php endif; ?></div>
		</div>
		<div class="card__body" style="border-bottom:1px solid var(--color-border)">
			<form method="get" action="<?= base_url('peminjaman/asset-loans') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem">
				<div style="flex:1 1 260px"><label class="text-xs text-muted-foreground" for="q">Cari proposal</label><input class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Identitas, nama, kegiatan, kode atau nama asset..."></div>
				<div style="flex:0 0 180px"><label class="text-xs text-muted-foreground" for="status">Status</label><select class="select" id="status" name="status">
						<option value="">Semua Status</option><?php foreach ($statusOptions as $option): ?><option value="<?= $option ?>" <?= $status === $option ? 'selected' : '' ?>><?= $statusLabels[$option] ?></option><?php endforeach; ?>
					</select></div><button class="button button--primary button--sm">Filter</button>
			</form>
		</div>
		<div class="card__body p-0">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<tr>
							<th>Pemohon</th>
							<th>Asset</th>
							<th>Kegiatan</th>
							<th>Status</th>
							<th class="text-center">Aksi</th>
						</tr>
					</thead>
					<tbody><?php foreach ($proposals as $proposal): ?><tr>
								<td><strong><?= esc($proposal['full_name']) ?></strong>
									<div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?></div>
								</td>
								<td><?= esc($proposal['asset_names'] ?: '-') ?></td>
								<td><strong><?= esc($proposal['event_name']) ?></strong>
									<div class="text-xs text-muted-foreground"><?= esc(date('d M Y H:i', strtotime($proposal['event_start']))) ?> - <?= esc(date('d M Y H:i', strtotime($proposal['event_end']))) ?></div>
								</td>
								<td><span class="badge badge--soft badge--<?= $statusColors[$proposal['status']] ?>"><?= $statusLabels[$proposal['status']] ?></span></td>
								<td class="text-center">
									<div class="flex justify-center gap-1"><?php if ($proposal['status'] !== 'draft'): ?><a class="button button--info button--icon-only button--sm" title="Detail" href="<?= base_url(($canApprove && $proposal['status'] === 'submitted' ? 'peminjaman/asset-loans/detail-approval/' : 'peminjaman/asset-loans/detail/') . $proposal['uuid']) ?>"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5a7.5 7.5 0 1 0 0 15a7.5 7.5 0 0 0 0-15Zm0 3.25v.5m0 2.5v4.5" /></svg></a><?php endif; ?><?php if ($proposal['status'] === 'draft'): ?><a class="button button--warning button--icon-only button--sm" title="Edit" href="<?= base_url('peminjaman/asset-loans/edit/' . $proposal['uuid']) ?>"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" /></svg></a><a class="button button--primary button--icon-only button--sm" title="Tambah Asset" href="<?= base_url('peminjaman/asset-loans/items/' . $proposal['uuid']) ?>"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 5v14m-7-7h14" /></svg></a>
											<form method="post" action="<?= base_url('peminjaman/asset-loans/delete/' . $proposal['uuid']) ?>" onsubmit="return confirm('Batalkan proposal ini?')"><?= csrf_field() ?><button class="button button--danger button--icon-only button--sm" title="Batalkan"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?><?php if ($proposal['status'] === 'approved' && activeGroupCan('loans.complete')): ?><a href="<?= base_url('peminjaman/asset-loans/returns/' . $proposal['uuid']) ?>" class="button button--success button--icon-only button--sm" title="Pengembalian"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 7V5.5A1.5 1.5 0 0 1 8.5 4h7A1.5 1.5 0 0 1 17 5.5v13A1.5 1.5 0 0 1 15.5 20h-7A1.5 1.5 0 0 1 7 18.5V17"/><path d="M3 12h12"/><path d="m13 9 3 3-3 3"/></svg></a><?php endif; ?>
									</div>
								</td>
							</tr><?php endforeach; ?><?php if (! $proposals): ?><tr>
								<td colspan="5" class="text-center text-muted-foreground py-8">Belum ada proposal peminjaman asset.</td>
							</tr><?php endif; ?></tbody>
				</table>
			</div>
		</div><?php if ($totalRows > 0): ?><div class="card__body" style="border-top:1px solid var(--color-border)">Total <?= $totalRows ?> proposal <?= $pager->links('default', 'app') ?></div><?php endif; ?>
	</div>
</div>

<div class="dialog dialog--sm" id="assetDeleteConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="assetDeleteConfirmLabel" aria-describedby="assetDeleteConfirmDesc" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
					<path d="M6 6l12 12M18 6L6 18" />
				</svg></button>
			<div class="dialog__body text-center pt-6"><span class="icon-box icon-box--danger icon-box--circle icon-box--lg mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
						<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 7h12m-9 0v10m6-10v10M8 7l.75-2h6.5L16 7m-9 0 .75 13h6.5L15 7" />
					</svg></span>
				<h3 class="dialog__title mb-1" id="assetDeleteConfirmLabel">Batalkan proposal?</h3>
				<p class="text-muted-foreground" id="assetDeleteConfirmDesc">Proposal <strong id="assetDeleteConfirmName"></strong> akan dibatalkan dan dihapus.</p>
			</div>
			<form id="assetDeleteForm" method="post"><?= csrf_field() ?><div class="dialog__footer justify-center"><button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button><button type="submit" class="button button--danger">Ya, Batalkan</button></div>
			</form>
			<script>
				document.addEventListener('DOMContentLoaded', function() {
					var dialog = document.getElementById('assetDeleteConfirm');
					var modalForm = document.getElementById('assetDeleteForm');
					var name = document.getElementById('assetDeleteConfirmName');
					var deleteForms = document.querySelectorAll('form[action*="/peminjaman/asset-loans/delete/"]');
					deleteForms.forEach(function(form) {
						form.removeAttribute('onsubmit');
						form.addEventListener('submit', function(event) {
							event.preventDefault();
							modalForm.action = form.action;
							name.textContent = form.closest('tr')?.querySelector('td:nth-child(3) strong')?.textContent || 'ini';
							dialog.dataset.state = 'open';
							dialog.setAttribute('aria-hidden', 'false');
							window.requestAnimationFrame(function() {
								modalForm.querySelector('[data-stisla-dialog-dismiss]').focus();
							});
						});
					});
					document.addEventListener('click', function(event) {
						if (!event.target.closest('[data-stisla-dialog-dismiss]')) return;
						var currentDialog = event.target.closest('[data-stisla-dialog]');
						if (!currentDialog) return;
						currentDialog.dataset.state = 'closed';
						currentDialog.setAttribute('aria-hidden', 'true');
					});
					modalForm.addEventListener('submit', function() {
						var submitButton = this.querySelector('button[type="submit"]');
						if (submitButton) submitButton.disabled = true;
					});
				});
			</script>
		</div>
	</div>
</div>