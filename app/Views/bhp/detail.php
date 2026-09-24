<?php
/** @var array $requestData */
/** @var array $items */
/** @var array $evidences */
/** @var array $history */
/** @var array $overrides */
$labels = ['DRAFT' => 'Draft', 'PENDING_REVIEW' => 'Menunggu Review', 'NEED_REVISION' => 'Perlu Revisi', 'APPROVED_BY_KALAB' => 'Disetujui', 'FUND_DISBURSED' => 'Anggaran Cair', 'EVIDEN_SUBMITTED' => 'Eviden Dikirim', 'COMPLETED' => 'Selesai', 'REJECTED' => 'Ditolak'];
$canReview = activeGroupIs('superadmin', 'kepala_lab');
?>
<style>
	.bhp-detail__panel { padding-top:1rem; }
	.bhp-detail__panel[hidden] { display:none; }
</style>
<div class="page__section flex flex-col gap-4"><div class="card"><div class="card__header"><div><span class="card__title"><?= esc($requestData['kode_pengajuan']) ?></span><div class="text-xs text-muted-foreground"><?= esc($requestData['uuid']) ?></div></div><div class="card__action"><span class="badge badge--soft badge--info"><?= esc($labels[$requestData['status']] ?? $requestData['status']) ?></span><a class="button button--outline button--sm" href="<?= base_url('bhp') ?>">Kembali</a></div></div><div class="card__body">
<div class="grid grid-cols-1 md:grid-cols-3 gap-4"><div><small>Estimasi</small><strong>Rp <?= number_format((float) $requestData['grand_total_estimasi'], 0, ',', '.') ?></strong></div><div><small>Nominal Cair</small><strong>Rp <?= number_format((float) $requestData['nominal_cair'], 0, ',', '.') ?></strong></div><div><small>Realisasi</small><strong>Rp <?= number_format((float) $requestData['realisasi_biaya'], 0, ',', '.') ?></strong><?php if ((float) $requestData['realisasi_biaya'] > (float) $requestData['grand_total_estimasi']): ?><span class="badge badge--soft badge--warning">Melebihi estimasi</span><?php endif; ?></div></div>
<?php if ($requestData['catatan_revisi']): ?><p class="text-warning">Catatan revisi: <?= esc($requestData['catatan_revisi']) ?></p><?php endif; ?><?php if ($requestData['alasan_penolakan']): ?><p class="text-danger">Alasan penolakan: <?= esc($requestData['alasan_penolakan']) ?></p><?php endif; ?>
<div style="border-top:1px solid var(--color-border);margin-top:1rem;padding-top:1rem;">
	<div class="toggle-group" data-stisla-toggle-group role="radiogroup" aria-label="Detail pengajuan BHP" style="display:inline-flex;width:max-content;max-width:100%;overflow-x:auto;">
		<button type="button" class="toggle" role="radio" aria-checked="true" data-state="active" aria-controls="bhp-detail-items" data-bhp-detail-tab="bhp-detail-items">Item Pengajuan</button>
		<button type="button" class="toggle" role="radio" aria-checked="false" data-state="inactive" aria-controls="bhp-detail-evidence" data-bhp-detail-tab="bhp-detail-evidence">Eviden</button>
		<button type="button" class="toggle" role="radio" aria-checked="false" data-state="inactive" aria-controls="bhp-detail-history" data-bhp-detail-tab="bhp-detail-history">History Perubahan Status</button>
	</div>
</div>
<section class="bhp-detail__panel" id="bhp-detail-items" role="tabpanel">
<h3>Item Pengajuan</h3>
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
				<tr>
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
		</tbody>
	</table>
</div>
<?php if (! empty($overrides)): ?><h3>Riwayat Override Item</h3><div class="table-responsive"><table class="table"><thead><tr><th>Waktu</th><th>Oleh</th><th>Perubahan</th><th>Alasan</th></tr></thead><tbody><?php foreach ($overrides as $override): $before = json_decode($override['before_data'], true) ?: []; $after = json_decode($override['after_data'], true) ?: []; ?><tr><td><?= esc($override['created_at']) ?></td><td><?= esc($override['changed_by_name']) ?></td><td><?= esc(($before['nama_barang'] ?? '-') . ' (' . ($before['qty'] ?? '-') . ') menjadi ' . ($after['nama_barang'] ?? '-') . ' (' . ($after['qty'] ?? '-') . ')') ?></td><td><?= esc($override['reason']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
<div class="flex gap-2" style="flex-wrap:wrap;margin-top:1rem"><?php if ($canReview && ($requestData['status'] === 'DRAFT' || $requestData['status'] === 'NEED_REVISION')): ?><form method="post" action="<?= base_url('bhp/submit/' . $requestData['uuid']) ?>"><?= csrf_field() ?><button class="button button--primary">Ajukan ke Review</button></form><?php endif; ?><?php if ($canReview && $requestData['status'] === 'APPROVED_BY_KALAB'): ?><form method="post" action="<?= base_url('bhp/disburse/' . $requestData['uuid']) ?>" class="flex gap-2"><?= csrf_field() ?><input class="input" type="date" name="tanggal_cair" required><input class="input" type="number" min="0" step="0.01" name="nominal_cair" placeholder="Nominal cair" required><button class="button button--primary">Tandai Anggaran Cair</button></form><?php endif; ?></div>
</section>
<section class="bhp-detail__panel" id="bhp-detail-evidence" role="tabpanel" hidden>
<?php if ($requestData['status'] === 'FUND_DISBURSED' && activeGroupCan('bhp.evidence')): ?><h3>Upload Eviden Belanja</h3><form method="post" enctype="multipart/form-data" action="<?= base_url('bhp/evidence/' . $requestData['uuid']) ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4"><?= csrf_field() ?><input class="input" type="date" name="tanggal_belanja" required><input class="input" type="number" min="0" step="0.01" name="realisasi_biaya" placeholder="Total realisasi" required><input class="input" type="file" name="foto_barang[]" accept="image/jpeg,image/png" multiple required><input class="input" type="file" name="dokumen_nota_kwitansi" accept="application/pdf,image/jpeg,image/png" required><textarea class="input" name="catatan_pembelian" placeholder="Catatan pembelian"></textarea><button class="button button--primary">Kirim Eviden</button></form><?php endif; ?>
<?php if ($requestData['status'] === 'EVIDEN_SUBMITTED' && $canReview): ?><h3>Verifikasi Eviden</h3><div class="flex gap-2"><form method="post" action="<?= base_url('bhp/verify/' . $requestData['uuid']) ?>"><?= csrf_field() ?><input class="input" name="note" placeholder="Catatan verifikasi"><button class="button button--success">Validasi & Tutup</button></form><form method="post" action="<?= base_url('bhp/reject-evidence/' . $requestData['uuid']) ?>"><?= csrf_field() ?><input class="input" name="note" placeholder="Feedback wajib" required><button class="button button--danger">Kembalikan</button></form></div><?php endif; ?>
<h3>File Eviden</h3><?php if (empty($evidences)): ?><?= view('partials/empty_table_state', ['message' => 'Belum ada file eviden.']) ?><?php else: ?><ul><?php foreach ($evidences as $evidence): ?><li><?= esc($evidence['tipe_file']) ?>: <a href="<?= base_url('bhp/evidence/' . $requestData['uuid'] . '/' . $evidence['uuid']) ?>"><?= esc($evidence['original_name']) ?></a></li><?php endforeach; ?></ul><?php endif; ?>
</section>
<section class="bhp-detail__panel" id="bhp-detail-history" role="tabpanel" hidden>
	<h3>History Perubahan Status</h3>
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
		tabs.forEach((tab) => tab.addEventListener('click', () => {
			tabs.forEach((item) => {
				const active = item === tab;
				item.setAttribute('aria-checked', active ? 'true' : 'false');
				item.dataset.state = active ? 'active' : 'inactive';
			});
			panels.forEach((panel) => { panel.hidden = panel.id !== tab.dataset.bhpDetailTab; });
		}));
	})();
</script>
