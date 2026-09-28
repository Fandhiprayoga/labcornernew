<?php
/** @var array $requestData */
/** @var array $items */
/** @var array $evidences */
/** @var array $history */
/** @var array $overrides */
/** @var array $laboranSubmissions */
$labels = ['DRAFT' => 'Draft', 'PENDING_REVIEW' => 'Menunggu Review', 'NEED_REVISION' => 'Perlu Revisi', 'APPROVED_BY_KALAB' => 'Disetujui', 'FUND_DISBURSED' => 'Anggaran Cair', 'EVIDEN_SUBMITTED' => 'Eviden Dikirim', 'COMPLETED' => 'Selesai', 'REJECTED' => 'Ditolak'];
$canReview = activeGroupIs('superadmin', 'kepala_lab');
$canDisburse = activeGroupIs('kepala_lab') && activeGroupCan('bhp.disburse') && $requestData['status'] === 'APPROVED_BY_KALAB';
$canDeleteEvidence = $requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence');
$canVerifyEvidence = activeGroupIs('kepala_lab') && activeGroupCan('bhp.verify') && $requestData['status'] === 'EVIDEN_SUBMITTED';
$laboranSummaries = [];
$laboranOptions = [];
$laboratoryOptions = [];
$readyLaboranIds = array_map('intval', array_column($laboranSubmissions ?? [], 'laboran_id'));
$evidencesByItem = [];
$generalEvidences = [];
foreach ($evidences as $evidence) {
	$itemId = (int) ($evidence['item_id'] ?? 0);
	if ($itemId > 0) {
		$evidencesByItem[$itemId][] = $evidence;
		continue;
	}
	$generalEvidences[] = $evidence;
}
foreach ($items as $item) {
	$laboranId = (int) ($item['laboran_id'] ?? 0);
	$laboranName = $item['laboran_name'] ?? 'Tidak diketahui';
	$laboratoryName = $item['laboratory_name'] ?? '-';
	if (! isset($laboranSummaries[$laboranId])) {
		$laboranSummaries[$laboranId] = ['id' => $laboranId, 'name' => $laboranName, 'laboratories' => [], 'item_count' => 0, 'total' => 0];
	}
	if (! empty($item['laboratory_name'])) {
		$laboranSummaries[$laboranId]['laboratories'][$item['laboratory_name']] = true;
	}
	$laboranOptions[$laboranName] = $laboranName;
	$laboratoryOptions[$laboratoryName] = $laboratoryName;
	$laboranSummaries[$laboranId]['item_count']++;
	$laboranSummaries[$laboranId]['total'] += (float) ($item['total_harga'] ?? 0);
}
ksort($laboranOptions);
ksort($laboratoryOptions);
?>
<style>
	.bhp-detail__overview { display:grid; grid-template-columns:minmax(0,2fr) minmax(18rem,1fr); gap:1rem; align-items:stretch; }
	.bhp-detail__metrics { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.75rem; }
	.bhp-detail__metric { border:1px solid var(--color-border); border-radius:.75rem; padding:1rem; background:var(--color-surface); min-width:0; }
	.bhp-detail__metric-label { color:var(--color-muted-foreground); font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; }
	.bhp-detail__metric-value { display:block; margin-top:.35rem; font-size:1.35rem; font-weight:700; line-height:1.2; color:var(--color-foreground); overflow-wrap:anywhere; }
	.bhp-detail__metric-note { margin-top:.35rem; font-size:.75rem; color:var(--color-muted-foreground); }
	.bhp-detail__submitters { border:1px solid color-mix(in srgb, var(--color-primary) 26%, var(--color-border)); border-radius:.75rem; padding:1rem; background:color-mix(in srgb, var(--color-primary) 8%, var(--color-surface)); }
	.bhp-detail__submitters-title { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.75rem; }
	.bhp-detail__submitters-list { display:flex; flex-direction:column; gap:.75rem; }
	.bhp-detail__submitter { display:flex; justify-content:space-between; gap:.75rem; padding-bottom:.75rem; border-bottom:1px solid color-mix(in srgb, var(--color-border) 80%, transparent); }
	.bhp-detail__submitter:last-child { padding-bottom:0; border-bottom:0; }
	.bhp-detail__submitter-meta { margin-top:.25rem; font-size:.75rem; color:var(--color-muted-foreground); }
	.bhp-detail__item-filters { display:flex; flex-wrap:wrap; align-items:flex-end; gap:.75rem; margin-bottom:1rem; }
	.bhp-detail__item-filter { flex:0 1 220px; min-width:180px; }
	.bhp-detail__panel { padding-top:1rem; }
	.bhp-detail__panel[hidden] { display:none; }
	@media (max-width:56rem) { .bhp-detail__overview { grid-template-columns:1fr; } .bhp-detail__metrics { grid-template-columns:1fr; } }
</style>
<div class="page__section flex flex-col gap-4"><div class="card"><div class="card__header"><div><span class="card__title"><?= esc($requestData['kode_pengajuan']) ?></span><div class="text-xs text-muted-foreground"><?= esc($requestData['uuid']) ?></div></div><div class="card__action"><span class="badge badge--soft badge--info"><?= esc($labels[$requestData['status']] ?? $requestData['status']) ?></span><a class="button button--outline button--sm" href="<?= base_url('bhp') ?>">Kembali</a></div></div><div class="card__body">
<div class="bhp-detail__overview">
	<div class="bhp-detail__metrics">
		<div class="bhp-detail__metric"><span class="bhp-detail__metric-label">Estimasi</span><strong class="bhp-detail__metric-value">Rp <?= number_format((float) $requestData['grand_total_estimasi'], 0, ',', '.') ?></strong><div class="bhp-detail__metric-note">Total estimasi semua item</div></div>
		<div class="bhp-detail__metric"><span class="bhp-detail__metric-label">Nominal Cair</span><strong class="bhp-detail__metric-value">Rp <?= number_format((float) $requestData['nominal_cair'], 0, ',', '.') ?></strong><div class="bhp-detail__metric-note">Dana yang sudah dicairkan</div><?php if ($canDisburse): ?><button type="button" class="button button--primary button--sm" style="margin-top:.75rem" data-bhp-disburse-open>Tandai Anggaran Cair</button><?php endif; ?></div>
		<div class="bhp-detail__metric"><span class="bhp-detail__metric-label">Realisasi</span><strong class="bhp-detail__metric-value">Rp <?= number_format((float) $requestData['realisasi_biaya'], 0, ',', '.') ?></strong><div class="bhp-detail__metric-note"><?php if ((float) $requestData['realisasi_biaya'] > (float) $requestData['grand_total_estimasi']): ?><span class="badge badge--soft badge--warning">Melebihi estimasi</span><?php else: ?>Total belanja aktual<?php endif; ?></div></div>
	</div>
	<div class="bhp-detail__submitters">
		<div class="bhp-detail__submitters-title"><div><strong>Laboran Pengaju</strong><div class="text-xs text-muted-foreground"><?= esc($requestData['prodi_snapshot'] ?: 'Program studi') ?></div></div><span class="badge badge--soft badge--primary"><?= count($laboranSummaries) ?> laboran</span></div>
		<?php if (empty($laboranSummaries)): ?>
			<div class="text-sm text-muted-foreground">Belum ada laboran yang mengajukan item.</div>
		<?php else: ?>
			<div class="bhp-detail__submitters-list">
				<?php foreach ($laboranSummaries as $summary): ?>
					<?php $isReadyForReview = in_array($summary['id'], $readyLaboranIds, true); ?>
					<div class="bhp-detail__submitter"><div><strong><?= esc($summary['name']) ?></strong><div class="bhp-detail__submitter-meta"><?= esc(implode(', ', array_keys($summary['laboratories'])) ?: '-') ?></div></div><div class="text-end"><span class="badge badge--soft badge--secondary"><?= (int) $summary['item_count'] ?> item</span><div class="bhp-detail__submitter-meta">Rp <?= number_format((float) $summary['total'], 0, ',', '.') ?></div><?php if ($isReadyForReview): ?><span class="badge badge--soft badge--success">Siap Review</span><?php elseif ($requestData['status'] === 'DRAFT' || $requestData['status'] === 'NEED_REVISION'): ?><?php if (activeGroupIs('laboran') && $summary['id'] === (int) auth()->id() && activeGroupCan('bhp.edit')): ?><form method="post" action="<?= base_url('bhp/ready/' . $requestData['uuid']) ?>" style="margin-top:.5rem"><?= csrf_field() ?><button class="button button--primary button--sm" type="submit">Siap Review</button></form><?php else: ?><span class="badge badge--soft badge--warning">Belum Siap</span><?php endif; ?><?php endif; ?></div></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php if ($requestData['catatan_revisi']): ?><p class="text-warning">Catatan revisi: <?= esc($requestData['catatan_revisi']) ?></p><?php endif; ?><?php if ($requestData['alasan_penolakan']): ?><p class="text-danger">Alasan penolakan: <?= esc($requestData['alasan_penolakan']) ?></p><?php endif; ?>
<div style="border-top:1px solid var(--color-border);margin-top:1rem;padding-top:1rem;">
	<div class="toggle-group" data-stisla-toggle-group role="radiogroup" aria-label="Detail pengajuan BHP" style="display:inline-flex;width:max-content;max-width:100%;overflow-x:auto;">
		<button type="button" class="toggle" role="radio" aria-checked="true" data-state="active" aria-controls="bhp-detail-items" data-bhp-detail-tab="bhp-detail-items">Item Pengajuan</button>
		<button type="button" class="toggle" role="radio" aria-checked="false" data-state="inactive" aria-controls="bhp-detail-evidence" data-bhp-detail-tab="bhp-detail-evidence">Eviden</button>
		<button type="button" class="toggle" role="radio" aria-checked="false" data-state="inactive" aria-controls="bhp-detail-history" data-bhp-detail-tab="bhp-detail-history">History Perubahan Status</button>
	</div>
</div>
<section class="bhp-detail__panel" id="bhp-detail-items" role="tabpanel">
<div class="bhp-detail__item-filters" data-bhp-item-filters>
	<div class="bhp-detail__item-filter">
		<label class="text-xs text-muted-foreground" for="bhp_item_filter_laboran">Laboran</label>
		<select class="select" id="bhp_item_filter_laboran" data-bhp-item-filter="laboran">
			<option value="">Semua laboran</option>
			<?php foreach ($laboranOptions as $laboranOption): ?><option value="<?= esc($laboranOption, 'attr') ?>"><?= esc($laboranOption) ?></option><?php endforeach; ?>
		</select>
	</div>
	<div class="bhp-detail__item-filter">
		<label class="text-xs text-muted-foreground" for="bhp_item_filter_laboratory">Laboratorium</label>
		<select class="select" id="bhp_item_filter_laboratory" data-bhp-item-filter="laboratory">
			<option value="">Semua laboratorium</option>
			<?php foreach ($laboratoryOptions as $laboratoryOption): ?><option value="<?= esc($laboratoryOption, 'attr') ?>"><?= esc($laboratoryOption) ?></option><?php endforeach; ?>
		</select>
	</div>
	<button type="button" class="button button--outline button--neutral button--sm" data-bhp-item-filter-reset>Reset</button>
</div>
<div class="table-responsive">
	<table class="table">
		<thead>
			<tr>
				<th>Laboran</th>
				<th>Laboratorium</th>
				<th>Barang</th>
				<th>Spesifikasi</th>
				<th>Qty</th>
				<th>Harga</th>
				<th>Total</th>
				<th>Vendor</th>
				<th>Link Toko</th>
				<?php if ($canReview && activeGroupCan('bhp.override')): ?><th>Override</th><?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($items)): ?>
				<tr><td colspan="<?= $canReview && activeGroupCan('bhp.override') ? 10 : 9 ?>"><?= view('partials/empty_table_state', ['message' => 'Belum ada item pengajuan.']) ?></td></tr>
			<?php endif; ?>
			<?php foreach ($items as $item): ?>
				<tr data-bhp-item-row data-laboran="<?= esc($item['laboran_name'] ?? 'Tidak diketahui', 'attr') ?>" data-laboratory="<?= esc($item['laboratory_name'] ?? '-', 'attr') ?>">
					<td><?= esc($item['laboran_name'] ?? '-') ?></td>
					<td><?= esc($item['laboratory_name'] ?? '-') ?></td>
					<td><?= esc($item['nama_barang']) ?></td>
					<td><?= esc($item['spesifikasi'] ?: '-') ?></td>
					<td><?= esc($item['qty'] . ' ' . $item['satuan']) ?></td>
					<td>Rp <?= number_format((float) $item['harga_satuan'], 0, ',', '.') ?></td>
					<td>Rp <?= number_format((float) $item['total_harga'], 0, ',', '.') ?></td>
					<td><?= esc($item['vendor']) ?></td>
					<td>
						<?php if (! empty($item['link_toko_online'])): ?>
							<a href="<?= esc($item['link_toko_online'], 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($item['link_toko_online']) ?></a>
						<?php else: ?>
							<span class="text-muted-foreground">-</span>
						<?php endif; ?>
					</td>
					<?php if ($canReview && activeGroupCan('bhp.override')): ?>
						<td><details><summary class="button button--warning button--sm">Override</summary><form method="post" action="<?= base_url('bhp/item/' . $item['uuid'] . '/override') ?>" class="flex flex-col gap-2" style="min-width:20rem;margin-top:.5rem"><?= csrf_field() ?><input class="input" name="nama_barang" value="<?= esc($item['nama_barang']) ?>" required><input class="input" name="spesifikasi" value="<?= esc($item['spesifikasi']) ?>"><input class="input" type="number" min="1" name="qty" value="<?= esc($item['qty']) ?>" required><input class="input" name="satuan" value="<?= esc($item['satuan']) ?>" required><input class="input" type="number" min="0" step="0.01" name="harga_satuan" value="<?= esc($item['harga_satuan']) ?>" required><input class="input" name="vendor" value="<?= esc($item['vendor']) ?>" required><input class="input" type="url" name="link_toko_online" value="<?= esc($item['link_toko_online']) ?>" required><textarea class="input" name="reason" placeholder="Alasan override wajib diisi" required></textarea><button class="button button--primary button--sm">Simpan Override</button></form></details></td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			<?php if (! empty($items)): ?>
				<tr data-bhp-item-empty-filter hidden><td colspan="<?= $canReview && activeGroupCan('bhp.override') ? 10 : 9 ?>"><?= view('partials/empty_table_state', ['message' => 'Tidak ada item yang sesuai dengan filter.']) ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
<?php if (! empty($overrides)): ?><div class="table-responsive" style="margin-top:1rem"><table class="table"><thead><tr><th>Waktu</th><th>Oleh</th><th>Perubahan</th><th>Alasan</th></tr></thead><tbody><?php foreach ($overrides as $override): $before = json_decode($override['before_data'], true) ?: []; $after = json_decode($override['after_data'], true) ?: []; ?><tr><td><?= esc($override['created_at']) ?></td><td><?= esc($override['changed_by_name']) ?></td><td><?= esc(($before['nama_barang'] ?? '-') . ' (' . ($before['qty'] ?? '-') . ') menjadi ' . ($after['nama_barang'] ?? '-') . ' (' . ($after['qty'] ?? '-') . ')') ?></td><td><?= esc($override['reason']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<section class="bhp-detail__panel" id="bhp-detail-evidence" role="tabpanel" hidden>
<div class="bhp-detail__item-filters" data-bhp-evidence-filters>
	<div class="bhp-detail__item-filter">
		<label class="text-xs text-muted-foreground" for="bhp_evidence_filter_laboran">Laboran</label>
		<select class="select" id="bhp_evidence_filter_laboran" data-bhp-evidence-filter="laboran">
			<option value="">Semua laboran</option>
			<?php foreach ($laboranOptions as $laboranOption): ?><option value="<?= esc($laboranOption, 'attr') ?>"><?= esc($laboranOption) ?></option><?php endforeach; ?>
		</select>
	</div>
	<div class="bhp-detail__item-filter">
		<label class="text-xs text-muted-foreground" for="bhp_evidence_filter_laboratory">Laboratorium</label>
		<select class="select" id="bhp_evidence_filter_laboratory" data-bhp-evidence-filter="laboratory">
			<option value="">Semua laboratorium</option>
			<?php foreach ($laboratoryOptions as $laboratoryOption): ?><option value="<?= esc($laboratoryOption, 'attr') ?>"><?= esc($laboratoryOption) ?></option><?php endforeach; ?>
		</select>
	</div>
	<div class="bhp-detail__item-filter">
		<label class="text-xs text-muted-foreground" for="bhp_evidence_filter_status">Status Eviden</label>
		<select class="select" id="bhp_evidence_filter_status" data-bhp-evidence-filter="status">
			<option value="">Semua status</option>
			<option value="complete">Sudah ada eviden</option>
			<option value="missing">Belum ada eviden</option>
		</select>
	</div>
	<button type="button" class="button button--outline button--neutral button--sm" data-bhp-evidence-filter-reset>Reset</button>
</div>
<div class="table-responsive">
	<table class="table">
		<thead>
			<tr>
				<th>Barang</th>
				<th>Laboratorium</th>
				<th>Qty</th>
				<th>Total Estimasi</th>
				<th>Realisasi</th>
				<th>Eviden</th>
				<?php if ($requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence')): ?><th class="text-end">Aksi</th><?php endif; ?>
			</tr>
		</thead>
		<tbody>
			<?php if (empty($items)): ?>
				<tr><td colspan="<?= $requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence') ? 7 : 6 ?>"><?= view('partials/empty_table_state', ['message' => 'Belum ada item pengajuan.']) ?></td></tr>
			<?php endif; ?>
			<?php foreach ($items as $item): ?>
				<?php $itemEvidences = $evidencesByItem[(int) $item['id']] ?? []; ?>
				<tr data-bhp-evidence-row data-laboran="<?= esc($item['laboran_name'] ?? 'Tidak diketahui', 'attr') ?>" data-laboratory="<?= esc($item['laboratory_name'] ?? '-', 'attr') ?>" data-evidence-status="<?= empty($itemEvidences) ? 'missing' : 'complete' ?>">
					<td><strong><?= esc($item['nama_barang']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($item['spesifikasi'] ?: '-') ?></div></td>
					<td><?= esc($item['laboratory_name'] ?? '-') ?></td>
					<td><?= esc($item['qty'] . ' ' . $item['satuan']) ?></td>
					<td>Rp <?= number_format((float) $item['total_harga'], 0, ',', '.') ?></td>
					<td><?= $item['realisasi_biaya'] !== null ? 'Rp ' . number_format((float) $item['realisasi_biaya'], 0, ',', '.') : '-' ?></td>
					<td>
						<?php if (empty($itemEvidences)): ?>
							<span class="badge badge--soft badge--warning">Belum ada eviden</span>
						<?php else: ?>
							<ul style="display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;margin:0;padding:0;list-style:none">
								<?php foreach ($itemEvidences as $evidence): ?>
									<?php $isPhotoEvidence = $evidence['tipe_file'] === 'FOTO_BARANG'; $previewLabel = $evidence['tipe_file'] === 'INVOICE' ? 'Preview invoice' : ($isPhotoEvidence ? 'Preview foto barang' : 'Preview nota/kwitansi'); ?>
									<li><button type="button" class="button button--<?= $isPhotoEvidence ? 'info' : 'primary' ?> button--icon-only button--sm" title="<?= $previewLabel ?>" aria-label="<?= $previewLabel ?>" data-bhp-evidence-preview data-preview-url="<?= esc(base_url('bhp/evidence/' . $requestData['uuid'] . '/' . $evidence['uuid']), 'attr') ?>" data-preview-title="<?= $previewLabel ?>" data-delete-url="<?= $canDeleteEvidence ? esc(base_url('bhp/evidence/' . $requestData['uuid'] . '/' . $evidence['uuid'] . '/delete'), 'attr') : '' ?>"><?php if ($isPhotoEvidence): ?><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6.5A1.5 1.5 0 0 1 5.5 5h2l1.2-1.5h6.6L16.5 5h2A1.5 1.5 0 0 1 20 6.5v11A1.5 1.5 0 0 1 18.5 19h-13A1.5 1.5 0 0 1 4 17.5v-11Z" /><circle cx="11.5" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="1.5" /></svg><?php else: ?><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 3.5h8l4 4V20.5H6z" /><path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.5" d="M14 3.5v4h4M8.5 12h7M8.5 15.5h7" /></svg><?php endif; ?></button></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</td>
					<?php if ($requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence')): ?>
					<td class="text-end"><button type="button" class="button button--primary button--icon-only button--sm" title="Input Eviden" aria-label="Input eviden <?= esc($item['nama_barang'], 'attr') ?>" data-bhp-evidence-open data-item-uuid="<?= esc($item['uuid'], 'attr') ?>" data-item-name="<?= esc($item['nama_barang'], 'attr') ?>"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg></button></td>
					<?php endif; ?>
				</tr>
			<?php endforeach; ?>
			<?php if (! empty($items)): ?>
				<tr data-bhp-evidence-empty-filter hidden><td colspan="<?= $requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence') ? 7 : 6 ?>"><?= view('partials/empty_table_state', ['message' => 'Tidak ada data eviden yang sesuai dengan filter.']) ?></td></tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
<?php if (! empty($generalEvidences)): ?><div style="margin-top:1rem"><strong>Eviden umum</strong><ul style="display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;margin:.5rem 0 0;padding:0;list-style:none"><?php foreach ($generalEvidences as $evidence): ?><?php $isPhotoEvidence = $evidence['tipe_file'] === 'FOTO_BARANG'; $previewLabel = $evidence['tipe_file'] === 'INVOICE' ? 'Preview invoice' : ($isPhotoEvidence ? 'Preview foto barang' : 'Preview nota/kwitansi'); ?><li><button type="button" class="button button--<?= $isPhotoEvidence ? 'info' : 'primary' ?> button--icon-only button--sm" title="<?= $previewLabel ?>" aria-label="<?= $previewLabel ?>" data-bhp-evidence-preview data-preview-url="<?= esc(base_url('bhp/evidence/' . $requestData['uuid'] . '/' . $evidence['uuid']), 'attr') ?>" data-preview-title="<?= $previewLabel ?>" data-delete-url="<?= $canDeleteEvidence ? esc(base_url('bhp/evidence/' . $requestData['uuid'] . '/' . $evidence['uuid'] . '/delete'), 'attr') : '' ?>"><?php if ($isPhotoEvidence): ?><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6.5A1.5 1.5 0 0 1 5.5 5h2l1.2-1.5h6.6L16.5 5h2A1.5 1.5 0 0 1 20 6.5v11A1.5 1.5 0 0 1 18.5 19h-13A1.5 1.5 0 0 1 4 17.5v-11Z" /><circle cx="11.5" cy="12" r="3.5" fill="none" stroke="currentColor" stroke-width="1.5" /></svg><?php else: ?><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 3.5h8l4 4V20.5H6z" /><path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.5" d="M14 3.5v4h4M8.5 12h7M8.5 15.5h7" /></svg><?php endif; ?></button></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if ($canVerifyEvidence): ?><div class="flex gap-2" style="margin-top:1rem"><button type="button" class="button button--success" data-bhp-verify-open>Validasi & Tutup</button><button type="button" class="button button--danger" data-bhp-reject-evidence-open>Kembalikan</button></div><?php endif; ?>
</section>
<section class="bhp-detail__panel" id="bhp-detail-history" role="tabpanel" hidden>
	<?php if (empty($history)): ?>
		<?= view('partials/empty_table_state', ['message' => 'Belum ada history perubahan status.']) ?>
	<?php else: ?>
		<div class="table-responsive">
			<table class="table">
				<thead><tr><th>Waktu</th><th>Perubahan Status</th><th>Oleh</th><th>Keterangan</th></tr></thead>
				<tbody>
					<?php foreach ($history as $entry): ?>
						<tr>
							<td><?= esc(date('d M Y H:i', strtotime($entry['created_at']))) ?></td>
							<td><?= esc($labels[$entry['from_status']] ?? 'Status awal') ?> &rarr; <strong><?= esc($labels[$entry['to_status']] ?? $entry['to_status']) ?></strong></td>
							<td><?= esc($entry['changed_by_name'] ?? '-') ?></td>
							<td><?= esc($entry['note'] ?: '-') ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</section>
</div></div></div>
<?php if ($canDisburse): ?>
<div class="dialog dialog--sm" id="bhpDisburseConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="bhpDisburseConfirmLabel" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<div class="dialog__header">
				<h3 class="dialog__title" id="bhpDisburseConfirmLabel">Tandai Anggaran Cair</h3>
				<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			</div>
			<form id="bhpDisburseForm" method="post" action="<?= base_url('bhp/disburse/' . $requestData['uuid']) ?>">
				<?= csrf_field() ?>
				<div class="dialog__body">
					<p class="text-muted-foreground text-sm mb-4">Masukkan nominal anggaran cair untuk pengajuan <strong><?= esc($requestData['kode_pengajuan']) ?></strong>.</p>
					<div class="field mb-3">
						<label class="field__label" for="bhp_disburse_nominal">Nominal Anggaran Cair <span class="text-danger">*</span></label>
						<input class="input" type="number" min="0" step="0.01" id="bhp_disburse_nominal" name="nominal_cair" value="<?= esc((string) ((float) ($requestData['nominal_cair'] ?: $requestData['grand_total_estimasi']))) ?>" required>
					</div>
					<div class="field">
						<label class="field__label" for="bhp_disburse_date">Tanggal Cair <span class="text-danger">*</span></label>
						<input class="input" type="date" id="bhp_disburse_date" name="tanggal_cair" value="<?= esc(date('Y-m-d')) ?>" required>
					</div>
				</div>
				<div class="dialog__footer">
					<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
					<button type="submit" class="button button--primary">Tandai Cair</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
<?php if ($requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence')): ?>
<div class="dialog" id="bhpEvidenceConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="bhpEvidenceConfirmLabel" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<div class="dialog__header">
				<h3 class="dialog__title" id="bhpEvidenceConfirmLabel">Input Eviden Item</h3>
				<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			</div>
			<form id="bhpEvidenceForm" method="post" enctype="multipart/form-data" action="<?= base_url('bhp/evidence/' . $requestData['uuid']) ?>">
				<?= csrf_field() ?>
				<input type="hidden" name="item_uuid" id="bhp_evidence_item_uuid">
				<div class="dialog__body">
					<p class="text-muted-foreground text-sm mb-4">Unggah eviden untuk item <strong data-slot="bhp-evidence-item-name"></strong>.</p>
					<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;">
						<div class="field"><label class="field__label" for="bhp_evidence_date">Tanggal Belanja <span class="text-danger">*</span></label><input class="input" type="date" id="bhp_evidence_date" name="tanggal_belanja" value="<?= esc(date('Y-m-d')) ?>" required></div>
						<div class="field"><label class="field__label" for="bhp_evidence_realization">Total Realisasi <span class="text-danger">*</span></label><input class="input" type="number" min="0" step="0.01" id="bhp_evidence_realization" name="realisasi_biaya" required></div>
						<div class="field"><label class="field__label" for="bhp_evidence_photos">Foto Barang <span class="text-danger">*</span></label><input class="input" type="file" id="bhp_evidence_photos" name="foto_barang[]" accept="image/jpeg,image/png" multiple required></div>
						<div class="field"><label class="field__label" for="bhp_evidence_receipt">Nota/Kwitansi <span class="text-danger">*</span></label><input class="input" type="file" id="bhp_evidence_receipt" name="dokumen_nota_kwitansi" accept="application/pdf,image/jpeg,image/png" required></div>
						<div class="field"><label class="field__label" for="bhp_evidence_invoice">Invoice <span class="text-danger">*</span></label><input class="input" type="file" id="bhp_evidence_invoice" name="dokumen_invoice" accept="application/pdf,image/jpeg,image/png" required></div>
					</div>
					<div class="field" style="margin-top:1rem;"><label class="field__label" for="bhp_evidence_note">Catatan Pembelian</label><textarea class="input" id="bhp_evidence_note" name="catatan_pembelian" rows="3" placeholder="Catatan pembelian"></textarea></div>
				</div>
				<div class="dialog__footer">
					<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
					<button type="submit" class="button button--primary">Kirim Eviden</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
<?php if ($canVerifyEvidence): ?>
<div class="dialog dialog--sm" id="bhpVerifyEvidenceConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="bhpVerifyEvidenceConfirmLabel" aria-describedby="bhpVerifyEvidenceConfirmDesc" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			<div class="dialog__body text-center pt-6">
				<span class="icon-box icon-box--success icon-box--circle icon-box--lg mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m5 12 4 4L19 6" /></svg></span>
				<h3 class="dialog__title mb-1" id="bhpVerifyEvidenceConfirmLabel">Validasi Evidence?</h3>
				<p class="text-muted-foreground" id="bhpVerifyEvidenceConfirmDesc">Evidence akan divalidasi dan pengajuan ditandai selesai.</p>
			</div>
			<form id="bhpVerifyEvidenceForm" method="post" action="<?= base_url('bhp/verify/' . $requestData['uuid']) ?>">
				<?= csrf_field() ?>
				<div class="dialog__body">
					<label class="field__label" for="bhp_verify_note">Remark Verifikasi</label>
					<textarea class="input" id="bhp_verify_note" name="note" rows="3" placeholder="Tambahkan remark verifikasi (opsional)"></textarea>
				</div>
				<div class="dialog__footer justify-center">
					<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
					<button type="submit" class="button button--success">Ya, Validasi</button>
				</div>
			</form>
		</div>
	</div>
</div>

<div class="dialog dialog--sm" id="bhpRejectEvidenceConfirm" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="bhpRejectEvidenceConfirmLabel" aria-describedby="bhpRejectEvidenceConfirmDesc" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<div class="dialog__header">
				<h3 class="dialog__title" id="bhpRejectEvidenceConfirmLabel">Kembalikan Evidence</h3>
				<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			</div>
			<form id="bhpRejectEvidenceForm" method="post" action="<?= base_url('bhp/reject-evidence/' . $requestData['uuid']) ?>">
				<?= csrf_field() ?>
				<div class="dialog__body">
					<p class="text-muted-foreground text-sm mb-4" id="bhpRejectEvidenceConfirmDesc">Evidence akan dikembalikan untuk diperbaiki oleh pengaju.</p>
					<label class="field__label" for="bhp_reject_evidence_note">Remark Pengembalian <span class="text-danger">*</span></label>
					<textarea class="input" id="bhp_reject_evidence_note" name="note" rows="3" required placeholder="Tuliskan alasan pengembalian evidence..."></textarea>
				</div>
				<div class="dialog__footer">
					<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
					<button type="submit" class="button button--danger">Kembalikan Evidence</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
<div class="dialog dialog--lg" id="bhpEvidencePreview" data-stisla-dialog data-state="closed" role="dialog" aria-modal="true" aria-labelledby="bhpEvidencePreviewLabel" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<div class="dialog__header">
				<h3 class="dialog__title" id="bhpEvidencePreviewLabel">Preview Evidence</h3>
				<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			</div>
			<div class="dialog__body" style="padding:0;min-height:24rem;background:var(--color-muted);">
				<iframe id="bhpEvidencePreviewFrame" title="Preview evidence" src="about:blank" style="display:block;width:100%;height:min(70vh,42rem);border:0;background:var(--color-surface);"></iframe>
			</div>
			<div class="dialog__footer">
				<form id="bhpEvidenceDeleteForm" method="post" style="margin-right:auto;display:none">
					<?= csrf_field() ?>
					<button type="button" class="button button--danger button--icon-only button--sm" title="Hapus evidence" aria-label="Hapus evidence" data-bhp-evidence-delete-open><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button>
				</form>
				<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Tutup</button>
			</div>
		</div>
	</div>
</div>
<?php if ($canDeleteEvidence): ?>
<div class="dialog dialog--sm" id="bhpEvidenceDeleteConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="bhpEvidenceDeleteConfirmLabel" aria-describedby="bhpEvidenceDeleteConfirmDesc" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			<div class="dialog__body text-center pt-6">
				<span class="icon-box icon-box--danger icon-box--circle icon-box--lg mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 7h12m-9 0v10m6-10v10M8 7l.75-2h6.5L16 7m-9 0 .75 13h6.5L15 7" /></svg></span>
				<h3 class="dialog__title mb-1" id="bhpEvidenceDeleteConfirmLabel">Hapus Evidence?</h3>
				<p class="text-muted-foreground" id="bhpEvidenceDeleteConfirmDesc">File evidence yang dihapus tidak dapat dipulihkan.</p>
			</div>
			<form id="bhpEvidenceDeleteConfirmForm" method="post">
				<?= csrf_field() ?>
				<div class="dialog__footer justify-center">
					<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
					<button type="submit" class="button button--danger">Ya, Hapus</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php endif; ?>
<script>
	(() => {
		const disburseDialog = document.getElementById('bhpDisburseConfirm');
		const disburseOpen = document.querySelector('[data-bhp-disburse-open]');
		const evidenceDialog = document.getElementById('bhpEvidenceConfirm');
		const evidenceForm = document.getElementById('bhpEvidenceForm');
		const evidencePreviewDialog = document.getElementById('bhpEvidencePreview');
		const evidencePreviewFrame = document.getElementById('bhpEvidencePreviewFrame');
		const evidenceDeleteForm = document.getElementById('bhpEvidenceDeleteForm');
		const evidenceDeleteOpen = document.querySelector('[data-bhp-evidence-delete-open]');
		const evidenceDeleteDialog = document.getElementById('bhpEvidenceDeleteConfirm');
		const evidenceDeleteConfirmForm = document.getElementById('bhpEvidenceDeleteConfirmForm');
		const verifyEvidenceOpen = document.querySelector('[data-bhp-verify-open]');
		const verifyEvidenceDialog = document.getElementById('bhpVerifyEvidenceConfirm');
		const verifyEvidenceForm = document.getElementById('bhpVerifyEvidenceForm');
		const rejectEvidenceOpen = document.querySelector('[data-bhp-reject-evidence-open]');
		const rejectEvidenceDialog = document.getElementById('bhpRejectEvidenceConfirm');
		const rejectEvidenceForm = document.getElementById('bhpRejectEvidenceForm');
		const tabs = document.querySelectorAll('[data-bhp-detail-tab]');
		const panels = document.querySelectorAll('.bhp-detail__panel');
		const itemFilterLaboran = document.querySelector('[data-bhp-item-filter="laboran"]');
		const itemFilterLaboratory = document.querySelector('[data-bhp-item-filter="laboratory"]');
		const itemFilterReset = document.querySelector('[data-bhp-item-filter-reset]');
		const itemRows = document.querySelectorAll('[data-bhp-item-row]');
		const itemEmptyFilter = document.querySelector('[data-bhp-item-empty-filter]');
		const evidenceFilterLaboran = document.querySelector('[data-bhp-evidence-filter="laboran"]');
		const evidenceFilterLaboratory = document.querySelector('[data-bhp-evidence-filter="laboratory"]');
		const evidenceFilterStatus = document.querySelector('[data-bhp-evidence-filter="status"]');
		const evidenceFilterReset = document.querySelector('[data-bhp-evidence-filter-reset]');
		const evidenceRows = document.querySelectorAll('[data-bhp-evidence-row]');
		const evidenceEmptyFilter = document.querySelector('[data-bhp-evidence-empty-filter]');
		const tabStorageKey = <?= json_encode('bhp-detail-active-tab-' . $requestData['uuid']) ?>;
		const activateTab = (tabId, persist = true) => {
			const selectedTab = Array.from(tabs).find((tab) => tab.dataset.bhpDetailTab === tabId);
			if (! selectedTab) return;

			tabs.forEach((item) => {
				const active = item === selectedTab;
				item.setAttribute('aria-checked', active ? 'true' : 'false');
				item.dataset.state = active ? 'active' : 'inactive';
			});
			panels.forEach((panel) => { panel.hidden = panel.id !== tabId; });
			if (persist) {
				try {
					localStorage.setItem(tabStorageKey, tabId);
				} catch (error) {
				}
			}
		};

		tabs.forEach((tab) => tab.addEventListener('click', () => activateTab(tab.dataset.bhpDetailTab)));
		try {
			activateTab(localStorage.getItem(tabStorageKey) || tabs[0]?.dataset.bhpDetailTab, false);
		} catch (error) {
			activateTab(tabs[0]?.dataset.bhpDetailTab, false);
		}

		const applyItemFilters = () => {
			const laboran = itemFilterLaboran ? itemFilterLaboran.value : '';
			const laboratory = itemFilterLaboratory ? itemFilterLaboratory.value : '';
			let visibleRows = 0;
			itemRows.forEach((row) => {
				const matchesLaboran = !laboran || row.dataset.laboran === laboran;
				const matchesLaboratory = !laboratory || row.dataset.laboratory === laboratory;
				const visible = matchesLaboran && matchesLaboratory;
				row.hidden = !visible;
				if (visible) visibleRows++;
			});
			if (itemEmptyFilter) itemEmptyFilter.hidden = visibleRows > 0;
		};

		if (itemFilterLaboran) itemFilterLaboran.addEventListener('change', applyItemFilters);
		if (itemFilterLaboratory) itemFilterLaboratory.addEventListener('change', applyItemFilters);
		if (itemFilterReset) itemFilterReset.addEventListener('click', () => {
			if (itemFilterLaboran) itemFilterLaboran.value = '';
			if (itemFilterLaboratory) itemFilterLaboratory.value = '';
			applyItemFilters();
		});

		const applyEvidenceFilters = () => {
			const laboran = evidenceFilterLaboran ? evidenceFilterLaboran.value : '';
			const laboratory = evidenceFilterLaboratory ? evidenceFilterLaboratory.value : '';
			const status = evidenceFilterStatus ? evidenceFilterStatus.value : '';
			let visibleRows = 0;
			evidenceRows.forEach((row) => {
				const matchesLaboran = !laboran || row.dataset.laboran === laboran;
				const matchesLaboratory = !laboratory || row.dataset.laboratory === laboratory;
				const matchesStatus = !status || row.dataset.evidenceStatus === status;
				const visible = matchesLaboran && matchesLaboratory && matchesStatus;
				row.hidden = !visible;
				if (visible) visibleRows++;
			});
			if (evidenceEmptyFilter) evidenceEmptyFilter.hidden = visibleRows > 0;
		};

		if (evidenceFilterLaboran) evidenceFilterLaboran.addEventListener('change', applyEvidenceFilters);
		if (evidenceFilterLaboratory) evidenceFilterLaboratory.addEventListener('change', applyEvidenceFilters);
		if (evidenceFilterStatus) evidenceFilterStatus.addEventListener('change', applyEvidenceFilters);
		if (evidenceFilterReset) evidenceFilterReset.addEventListener('click', () => {
			if (evidenceFilterLaboran) evidenceFilterLaboran.value = '';
			if (evidenceFilterLaboratory) evidenceFilterLaboratory.value = '';
			if (evidenceFilterStatus) evidenceFilterStatus.value = '';
			applyEvidenceFilters();
		});

		if (disburseOpen && disburseDialog) {
			disburseOpen.addEventListener('click', () => {
				disburseDialog.dataset.state = 'open';
				disburseDialog.setAttribute('aria-hidden', 'false');
				window.requestAnimationFrame(() => {
					const nominalInput = disburseDialog.querySelector('#bhp_disburse_nominal');
					if (nominalInput) nominalInput.focus();
				});
			});

			disburseDialog.querySelectorAll('[data-stisla-dialog-dismiss]').forEach((element) => {
				element.addEventListener('click', () => {
					disburseDialog.dataset.state = 'closed';
					disburseDialog.setAttribute('aria-hidden', 'true');
				});
			});

			const disburseForm = document.getElementById('bhpDisburseForm');
			if (disburseForm) disburseForm.addEventListener('submit', () => {
				const submitButton = disburseForm.querySelector('button[type="submit"]');
				if (submitButton) submitButton.disabled = true;
			});
		}

		if (evidenceDialog && evidenceForm) {
			document.querySelectorAll('[data-bhp-evidence-open]').forEach((button) => {
				button.addEventListener('click', () => {
					const itemUuid = document.getElementById('bhp_evidence_item_uuid');
					const itemSlot = evidenceDialog.querySelector('[data-slot="bhp-evidence-item-name"]');
					if (itemUuid) itemUuid.value = button.dataset.itemUuid || '';
					if (itemSlot) itemSlot.textContent = button.dataset.itemName || 'ini';
					evidenceForm.reset();
					if (itemUuid) itemUuid.value = button.dataset.itemUuid || '';
					evidenceDialog.dataset.state = 'open';
					evidenceDialog.setAttribute('aria-hidden', 'false');
					window.requestAnimationFrame(() => {
						const dateInput = document.getElementById('bhp_evidence_date');
						if (dateInput) dateInput.focus();
					});
				});
			});

			evidenceDialog.querySelectorAll('[data-stisla-dialog-dismiss]').forEach((element) => {
				element.addEventListener('click', () => {
					evidenceDialog.dataset.state = 'closed';
					evidenceDialog.setAttribute('aria-hidden', 'true');
				});
			});

			evidenceForm.addEventListener('submit', () => {
				const submitButton = evidenceForm.querySelector('button[type="submit"]');
				if (submitButton) submitButton.disabled = true;
			});
		}

		if (evidencePreviewDialog && evidencePreviewFrame) {
			document.querySelectorAll('[data-bhp-evidence-preview]').forEach((button) => {
				button.addEventListener('click', () => {
					evidencePreviewFrame.src = button.dataset.previewUrl || 'about:blank';
					if (evidenceDeleteForm) {
						evidenceDeleteForm.action = button.dataset.deleteUrl || '';
						evidenceDeleteForm.style.display = button.dataset.deleteUrl ? 'block' : 'none';
					}
					const title = button.dataset.previewTitle || 'Preview Evidence';
					const titleElement = document.getElementById('bhpEvidencePreviewLabel');
					if (titleElement) titleElement.textContent = title;
					evidencePreviewDialog.dataset.state = 'open';
					evidencePreviewDialog.setAttribute('aria-hidden', 'false');
				});
			});

			evidencePreviewDialog.querySelectorAll('[data-stisla-dialog-dismiss]').forEach((element) => {
				element.addEventListener('click', () => {
					evidencePreviewDialog.dataset.state = 'closed';
					evidencePreviewDialog.setAttribute('aria-hidden', 'true');
					evidencePreviewFrame.src = 'about:blank';
					if (evidenceDeleteForm) {
						evidenceDeleteForm.action = '';
						evidenceDeleteForm.style.display = 'none';
					}
				});
			});
		}

		if (evidenceDeleteOpen && evidenceDeleteDialog && evidenceDeleteForm && evidenceDeleteConfirmForm) {
			evidenceDeleteOpen.addEventListener('click', () => {
				evidenceDeleteConfirmForm.action = evidenceDeleteForm.action;
				evidenceDeleteDialog.dataset.state = 'open';
				evidenceDeleteDialog.setAttribute('aria-hidden', 'false');
			});

			evidenceDeleteDialog.querySelectorAll('[data-stisla-dialog-dismiss]').forEach((element) => {
				element.addEventListener('click', () => {
					evidenceDeleteDialog.dataset.state = 'closed';
					evidenceDeleteDialog.setAttribute('aria-hidden', 'true');
				});
			});

			evidenceDeleteConfirmForm.addEventListener('submit', () => {
				const submitButton = evidenceDeleteConfirmForm.querySelector('button[type="submit"]');
				if (submitButton) submitButton.disabled = true;
			});
		}

		if (verifyEvidenceOpen && verifyEvidenceDialog && verifyEvidenceForm) {
			verifyEvidenceOpen.addEventListener('click', () => {
				verifyEvidenceDialog.dataset.state = 'open';
				verifyEvidenceDialog.setAttribute('aria-hidden', 'false');
				window.requestAnimationFrame(() => {
					const note = verifyEvidenceForm.querySelector('textarea[name="note"]');
					if (note) note.focus();
				});
			});
			verifyEvidenceDialog.querySelectorAll('[data-stisla-dialog-dismiss]').forEach((element) => {
				element.addEventListener('click', () => {
					verifyEvidenceDialog.dataset.state = 'closed';
					verifyEvidenceDialog.setAttribute('aria-hidden', 'true');
				});
			});
			verifyEvidenceForm.addEventListener('submit', () => {
				const submitButton = verifyEvidenceForm.querySelector('button[type="submit"]');
				if (submitButton) submitButton.disabled = true;
			});
		}

		if (rejectEvidenceOpen && rejectEvidenceDialog && rejectEvidenceForm) {
			rejectEvidenceOpen.addEventListener('click', () => {
				const note = rejectEvidenceForm.querySelector('textarea[name="note"]');
				if (note) note.value = '';
				rejectEvidenceDialog.dataset.state = 'open';
				rejectEvidenceDialog.setAttribute('aria-hidden', 'false');
				window.requestAnimationFrame(() => {
					if (note) note.focus();
				});
			});
			rejectEvidenceDialog.querySelectorAll('[data-stisla-dialog-dismiss]').forEach((element) => {
				element.addEventListener('click', () => {
					rejectEvidenceDialog.dataset.state = 'closed';
					rejectEvidenceDialog.setAttribute('aria-hidden', 'true');
				});
			});
			rejectEvidenceForm.addEventListener('submit', () => {
				const submitButton = rejectEvidenceForm.querySelector('button[type="submit"]');
				if (submitButton) submitButton.disabled = true;
			});
		}
	})();
</script>
