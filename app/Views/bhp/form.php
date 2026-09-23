<?php
/** @var array|null $requestData */
/** @var array|null $period */
/** @var array $periods */
/** @var array $laboratories */
/** @var array $studyPrograms */
/** @var array $laboratoryStudyPrograms */
/** @var array $studyProgramLaboratories */
/** @var array $units */
/** @var array $items */
/** @var int $selectedStudyProgramId */
$isEdit = ! empty($requestData);
$items = $items ?? [[]];
$periodId = old('periode_id', $requestData['periode_id'] ?? ($selectedPeriodId ?? ($period['id'] ?? '')));
$programId = old('study_program_id', $requestData['study_program_id'] ?? ($selectedStudyProgramId ?? ''));
$selectedProgram = $requestData['prodi_snapshot'] ?? ($pocket['prodi_snapshot'] ?? '');
if ($selectedProgram === '') {
	foreach (($studyPrograms ?? []) as $program) {
		if ((string) $program['id'] === (string) $programId) {
			$selectedProgram = $program['code'] . ' - ' . $program['name'];
			break;
		}
	}
}
$oldLaboratories = old('item_laboratory_id', []);
?>
<div class="page__section"><div class="card"><div class="card__header"><span class="card__title"><?= $isEdit ? 'Edit' : 'Tambah' ?> Item BHP</span></div><div class="card__body">
<form method="post" action="<?= base_url('bhp/' . ($isEdit ? 'update/' . $requestData['uuid'] : 'store')) ?>" class="flex flex-col gap-4"><?= csrf_field() ?>
<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;"><div class="field"><label class="field__label" for="periode_display">Periode</label><input class="input w-full" id="periode_display" value="<?= esc($period['nama_periode'] ?? '-') ?>" readonly><input type="hidden" name="periode_id" value="<?= esc($periodId) ?>"></div><div class="field"><label class="field__label" for="study_program_display">Program Studi</label><input class="input w-full" id="study_program_display" value="<?= esc($selectedProgram ?: '-') ?>" readonly><input type="hidden" name="study_program_id" value="<?= esc($programId) ?>"></div></div>
<div class="table-responsive"><table class="table" id="bhp-items"><thead><tr><th>Laboratorium</th><th>Nama Barang</th><th>Spesifikasi</th><th>Qty</th><th>Satuan</th><th>Harga Satuan</th><th>Vendor</th><th>Link Toko</th><th></th></tr></thead><tbody><?php foreach ($items as $index => $item): ?><tr><td><select class="input item-laboratory" name="item_laboratory_id[]" required data-selected="<?= esc($oldLaboratories[$index] ?? ($item['laboratory_id'] ?? '')) ?>"><option value="">Pilih lab</option></select></td><td><input class="input" name="nama_barang[]" value="<?= esc($item['nama_barang'] ?? '') ?>" required></td><td><input class="input" name="spesifikasi[]" value="<?= esc($item['spesifikasi'] ?? '') ?>"></td><td><input class="input" type="number" min="1" name="qty[]" value="<?= esc($item['qty'] ?? 1) ?>" required></td><td><select class="input" name="satuan[]"><?php foreach ($units as $unit): ?><option <?= ($item['satuan'] ?? '') === $unit ? 'selected' : '' ?>><?= esc($unit) ?></option><?php endforeach; ?></select></td><td><input class="input" type="number" min="0" step="0.01" name="harga_satuan[]" value="<?= esc($item['harga_satuan'] ?? '') ?>" required></td><td><input class="input" name="vendor[]" value="<?= esc($item['vendor'] ?? '') ?>" required></td><td><input class="input" type="url" name="link_toko_online[]" value="<?= esc($item['link_toko_online'] ?? '') ?>" required></td><td><button type="button" class="button button--danger button--sm" data-remove>Hapus</button></td></tr><?php endforeach; ?></tbody></table></div>
<button type="button" class="button button--outline button--sm" id="add-item">Tambah Baris Item</button><div class="flex justify-end gap-2"><a class="button button--outline button--neutral" href="<?= base_url('bhp') ?>">Batal</a><button class="button button--primary" type="submit">Simpan Item</button></div>
</form></div></div></div>
<script>
(() => { const body = document.querySelector('#bhp-items tbody'); const template = body.querySelector('tr').cloneNode(true); const studyProgram = document.querySelector('input[name="study_program_id"]'); const mappings = <?= json_encode($studyProgramLaboratories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>; const refreshLaboratories = () => { body.querySelectorAll('.item-laboratory').forEach((select) => { const selected = select.value || select.dataset.selected || ''; const options = mappings[studyProgram.value] || []; select.innerHTML = '<option value="">' + (options.length ? 'Pilih lab' : 'Tidak ada lab terdaftar') + '</option>'; options.forEach((lab) => { const option = document.createElement('option'); option.value = lab.id; option.textContent = lab.name; if (String(lab.id) === String(selected)) option.selected = true; select.add(option); }); select.disabled = options.length === 0; }); }; refreshLaboratories(); document.querySelector('#add-item').addEventListener('click', () => { const row = template.cloneNode(true); row.querySelectorAll('input').forEach((input) => { input.value = input.name === 'qty[]' ? '1' : ''; }); row.querySelector('.item-laboratory').value = ''; row.querySelector('.item-laboratory').dataset.selected = ''; body.appendChild(row); refreshLaboratories(); }); body.addEventListener('click', (event) => { if (event.target.matches('[data-remove]') && body.rows.length > 1) event.target.closest('tr').remove(); }); })();
</script>
