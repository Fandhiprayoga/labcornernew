<?php
/** @var array|null $requestData */
/** @var array|null $period */
/** @var array $periods */
/** @var array $laboratories */
/** @var array $studyPrograms */
/** @var array $units */
/** @var array $items */
$isEdit = ! empty($requestData);
$items = $items ?? [[]];
$periodId = old('periode_id', $requestData['periode_id'] ?? ($period['id'] ?? ''));
$labId = old('laboratory_id', $requestData['laboratory_id'] ?? '');
$programId = old('study_program_id', $requestData['study_program_id'] ?? '');
?>
<div class="page__section"><div class="card"><div class="card__header"><span class="card__title"><?= $isEdit ? 'Edit' : 'Buat' ?> Pengajuan BHP</span></div><div class="card__body">
<form method="post" action="<?= base_url('bhp/' . ($isEdit ? 'update/' . $requestData['uuid'] : 'store')) ?>" class="flex flex-col gap-4"><?= csrf_field() ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4"><div class="field"><label class="field__label">Periode</label><select class="input w-full" name="periode_id" required><option value="">Pilih periode</option><?php foreach (($periods ?? []) as $availablePeriod): ?><option value="<?= $availablePeriod['id'] ?>" <?= (string) $periodId === (string) $availablePeriod['id'] ? 'selected' : '' ?>><?= esc($availablePeriod['nama_periode']) ?></option><?php endforeach; ?></select></div><div class="field"><label class="field__label">Laboratorium</label><select class="input w-full" name="laboratory_id" required><option value="">Pilih laboratorium</option><?php foreach ($laboratories as $lab): ?><option value="<?= $lab['id'] ?>" <?= (string) $labId === (string) $lab['id'] ? 'selected' : '' ?>><?= esc($lab['name']) ?></option><?php endforeach; ?></select></div><div class="field"><label class="field__label">Program Studi</label><select class="input w-full" name="study_program_id"><option value="">Pilih prodi</option><?php foreach ($studyPrograms as $program): ?><option value="<?= $program['id'] ?>" <?= (string) $programId === (string) $program['id'] ? 'selected' : '' ?>><?= esc($program['code'] . ' - ' . $program['name']) ?></option><?php endforeach; ?></select></div></div>
<div class="table-responsive"><table class="table" id="bhp-items"><thead><tr><th>Nama Barang</th><th>Spesifikasi</th><th>Qty</th><th>Satuan</th><th>Harga Satuan</th><th>Vendor</th><th>Link Toko</th><th></th></tr></thead><tbody><?php foreach ($items as $item): ?><tr><td><input class="input" name="nama_barang[]" value="<?= esc($item['nama_barang'] ?? '') ?>" required></td><td><input class="input" name="spesifikasi[]" value="<?= esc($item['spesifikasi'] ?? '') ?>"></td><td><input class="input" type="number" min="1" name="qty[]" value="<?= esc($item['qty'] ?? 1) ?>" required></td><td><select class="input" name="satuan[]"><?php foreach ($units as $unit): ?><option <?= ($item['satuan'] ?? '') === $unit ? 'selected' : '' ?>><?= esc($unit) ?></option><?php endforeach; ?></select></td><td><input class="input" type="number" min="0" step="0.01" name="harga_satuan[]" value="<?= esc($item['harga_satuan'] ?? '') ?>" required></td><td><input class="input" name="vendor[]" value="<?= esc($item['vendor'] ?? '') ?>" required></td><td><input class="input" type="url" name="link_toko_online[]" value="<?= esc($item['link_toko_online'] ?? '') ?>" required></td><td><button type="button" class="button button--danger button--sm" data-remove>Hapus</button></td></tr><?php endforeach; ?></tbody></table></div>
<button type="button" class="button button--outline button--sm" id="add-item">Tambah Item</button><div class="flex justify-end gap-2"><a class="button button--outline button--neutral" href="<?= base_url('bhp') ?>">Batal</a><button class="button button--primary" type="submit">Simpan Draft</button></div>
</form></div></div></div>
<script>
(() => { const body = document.querySelector('#bhp-items tbody'); const template = body.querySelector('tr').cloneNode(true); document.querySelector('#add-item').addEventListener('click', () => { const row = template.cloneNode(true); row.querySelectorAll('input').forEach((input) => input.value = input.name === 'qty[]' ? '1' : ''); body.appendChild(row); }); body.addEventListener('click', (event) => { if (event.target.matches('[data-remove]') && body.rows.length > 1) event.target.closest('tr').remove(); }); })();
</script>
