<?php
/** @var array $requestData */
/** @var array $items */
/** @var array $evidences */
/** @var array $history */
/** @var array $overrides */
/** @var array $laboranSubmissions */
$labels = ['DRAFT' => 'Draft', 'PENDING_REVIEW' => 'Menunggu Review', 'NEED_REVISION' => 'Perlu Revisi', 'APPROVED_BY_KALAB' => 'Disetujui', 'FUND_DISBURSED' => 'Anggaran Cair', 'EVIDEN_SUBMITTED' => 'Eviden Dikirim', 'COMPLETED' => 'Selesai', 'REJECTED' => 'Ditolak'];
$canReview = activeGroupIs('superadmin', 'kepala_lab');
$laboranSummaries = [];
$laboranOptions = [];
$laboratoryOptions = [];
$readyLaboranIds = array_map('intval', array_column($laboranSubmissions ?? [], 'laboran_id'));
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
		<div class="bhp-detail__metric"><span class="bhp-detail__metric-label">Nominal Cair</span><strong class="bhp-detail__metric-value">Rp <?= number_format((float) $requestData['nominal_cair'], 0, ',', '.') ?></strong><div class="bhp-detail__metric-note">Dana yang sudah dicairkan</div></div>
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
<div class="flex gap-2" style="flex-wrap:wrap;margin-top:1rem"><?php if ($canReview && $requestData['status'] === 'APPROVED_BY_KALAB'): ?><form method="post" action="<?= base_url('bhp/disburse/' . $requestData['uuid']) ?>" class="flex gap-2"><?= csrf_field() ?><input class="input" type="date" name="tanggal_cair" required><input class="input" type="number" min="0" step="0.01" name="nominal_cair" placeholder="Nominal cair" required><button class="button button--primary">Tandai Anggaran Cair</button></form><?php endif; ?></div>
</section>
<section class="bhp-detail__panel" id="bhp-detail-evidence" role="tabpanel" hidden>
<?php if ($requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence')): ?><form method="post" enctype="multipart/form-data" action="<?= base_url('bhp/evidence/' . $requestData['uuid']) ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4"><?= csrf_field() ?><input class="input" type="date" name="tanggal_belanja" required><input class="input" type="number" min="0" step="0.01" name="realisasi_biaya" placeholder="Total realisasi" required><input class="input" type="file" name="foto_barang[]" accept="image/jpeg,image/png" multiple required><input class="input" type="file" name="dokumen_nota_kwitansi" accept="application/pdf,image/jpeg,image/png" required><textarea class="input" name="catatan_pembelian" placeholder="Catatan pembelian"></textarea><button class="button button--primary">Kirim Eviden</button></form><?php endif; ?>
<?php if ($requestData['status'] === 'EVIDEN_SUBMITTED' && $canReview): ?><div class="flex gap-2" style="margin-top:1rem"><form method="post" action="<?= base_url('bhp/verify/' . $requestData['uuid']) ?>"><?= csrf_field() ?><input class="input" name="note" placeholder="Catatan verifikasi"><button class="button button--success">Validasi & Tutup</button></form><form method="post" action="<?= base_url('bhp/reject-evidence/' . $requestData['uuid']) ?>"><?= csrf_field() ?><input class="input" name="note" placeholder="Feedback wajib" required><button class="button button--danger">Kembalikan</button></form></div><?php endif; ?>
<div style="margin-top:1rem"><?php if (empty($evidences)): ?><?= view('partials/empty_table_state', ['message' => 'Belum ada file eviden.']) ?><?php else: ?><ul><?php foreach ($evidences as $evidence): ?><li><?= esc($evidence['tipe_file']) ?>: <a href="<?= base_url('bhp/evidence/' . $requestData['uuid'] . '/' . $evidence['uuid']) ?>"><?= esc($evidence['original_name']) ?></a></li><?php endforeach; ?></ul><?php endif; ?></div>
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
<script>
	(() => {
		const tabs = document.querySelectorAll('[data-bhp-detail-tab]');
		const panels = document.querySelectorAll('.bhp-detail__panel');
		const itemFilterLaboran = document.querySelector('[data-bhp-item-filter="laboran"]');
		const itemFilterLaboratory = document.querySelector('[data-bhp-item-filter="laboratory"]');
		const itemFilterReset = document.querySelector('[data-bhp-item-filter-reset]');
		const itemRows = document.querySelectorAll('[data-bhp-item-row]');
		const itemEmptyFilter = document.querySelector('[data-bhp-item-empty-filter]');
		tabs.forEach((tab) => tab.addEventListener('click', () => {
			tabs.forEach((item) => {
				const active = item === tab;
				item.setAttribute('aria-checked', active ? 'true' : 'false');
				item.dataset.state = active ? 'active' : 'inactive';
			});
			panels.forEach((panel) => { panel.hidden = panel.id !== tab.dataset.bhpDetailTab; });
		}));

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
	})();
</script>
