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
$cancelUrl = base_url('bhp');
?>
<style>
	.bhp-add-item-button {
		background: var(--color-success);
		border-color: var(--color-success);
		color: #fff;
		box-shadow: 0 .5rem 1rem rgba(22, 163, 74, .18);
	}

	.bhp-add-item-button:hover,
	.bhp-add-item-button:focus {
		background: color-mix(in srgb, var(--color-success) 88%, #000);
		border-color: color-mix(in srgb, var(--color-success) 88%, #000);
		color: #fff;
	}
</style>
<div class="page__section"><div class="card"><div class="card__header"><span class="card__title"><?= $isEdit ? 'Edit' : 'Tambah' ?> Item BHP</span></div><div class="card__body">
<form id="bhpItemForm" method="post" action="<?= base_url('bhp/' . ($isEdit ? 'update/' . $requestData['uuid'] : 'store')) ?>" class="flex flex-col gap-4"><?= csrf_field() ?>
<div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;"><div class="field"><label class="field__label" for="periode_display">Periode</label><input class="input w-full" id="periode_display" value="<?= esc($period['nama_periode'] ?? '-') ?>" readonly><input type="hidden" name="periode_id" value="<?= esc($periodId) ?>"></div><div class="field"><label class="field__label" for="study_program_display">Program Studi</label><input class="input w-full" id="study_program_display" value="<?= esc($selectedProgram ?: '-') ?>" readonly><input type="hidden" name="study_program_id" value="<?= esc($programId) ?>"></div></div>
<div class="table-responsive"><table class="table" id="bhp-items"><thead><tr><th>Laboratorium</th><th>Nama Barang</th><th>Spesifikasi</th><th>Qty</th><th>Satuan</th><th>Harga Satuan</th><th>Vendor</th><th>Link Toko</th><th></th></tr></thead><tbody><?php foreach ($items as $index => $item): ?><tr><td><select class="input item-laboratory" name="item_laboratory_id[]" required data-selected="<?= esc($oldLaboratories[$index] ?? ($item['laboratory_id'] ?? '')) ?>"><option value="">Pilih lab</option></select></td><td><input class="input" name="nama_barang[]" value="<?= esc($item['nama_barang'] ?? '') ?>" required></td><td><input class="input" name="spesifikasi[]" value="<?= esc($item['spesifikasi'] ?? '') ?>"></td><td><input class="input" type="number" min="1" name="qty[]" value="<?= esc($item['qty'] ?? 1) ?>" required></td><td><select class="input" name="satuan[]"><?php foreach ($units as $unit): ?><option <?= ($item['satuan'] ?? '') === $unit ? 'selected' : '' ?>><?= esc($unit) ?></option><?php endforeach; ?></select></td><td><input class="input" type="number" min="0" step="0.01" name="harga_satuan[]" value="<?= esc($item['harga_satuan'] ?? '') ?>" required></td><td><input class="input" name="vendor[]" value="<?= esc($item['vendor'] ?? '') ?>" required></td><td><input class="input" type="url" name="link_toko_online[]" value="<?= esc($item['link_toko_online'] ?? '') ?>" required></td><td><button type="button" class="button button--danger button--icon-only button--sm" data-remove title="Hapus item" aria-label="Hapus item"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7h16m-10 4v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3" /></svg></button></td></tr><?php endforeach; ?></tbody></table></div>
<button type="button" class="button button--primary button--sm bhp-add-item-button" id="add-item"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.75" d="M12 5v14m-7-7h14" /></svg>Tambah Baris Item</button><div class="flex justify-end gap-2"><button type="button" class="button button--outline button--neutral" id="openBhpCancelConfirm">Batal</button><button class="button button--primary" type="button" id="openBhpSaveConfirm">Simpan Item</button></div>
</form></div></div></div>

<div class="dialog dialog--sm" id="bhpSaveConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="bhpSaveConfirmLabel" aria-describedby="bhpSaveConfirmDesc" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			<div class="dialog__body text-center pt-6">
				<span class="icon-box icon-box--primary icon-box--circle icon-box--lg mb-3">
					<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 12h14m-6-6 6 6-6 6" /></svg>
				</span>
				<h3 class="dialog__title mb-1" id="bhpSaveConfirmLabel">Simpan item BHP?</h3>
				<p class="text-muted-foreground" id="bhpSaveConfirmDesc">Data item BHP akan disimpan setelah Anda mengonfirmasi.</p>
			</div>
			<div class="dialog__footer justify-center">
				<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Batal</button>
				<button type="button" class="button button--primary" id="confirmBhpSave">Ya, Simpan</button>
			</div>
		</div>
	</div>
</div>

<div class="dialog dialog--sm" id="bhpCancelConfirm" data-stisla-dialog data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="bhpCancelConfirmLabel" aria-describedby="bhpCancelConfirmDesc" aria-hidden="true" tabindex="-1">
	<div class="dialog__backdrop" data-stisla-dialog-dismiss></div>
	<div class="dialog__panel">
		<div class="dialog__content">
			<button type="button" class="dialog__close" data-stisla-dialog-dismiss aria-label="Tutup">&times;</button>
			<div class="dialog__body text-center pt-6">
				<span class="icon-box icon-box--warning icon-box--circle icon-box--lg mb-3">
					<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v4m0 4h.01M10.3 4.3 2.8 17.3A2 2 0 0 0 4.5 20h15a2 2 0 0 0 1.7-2.7L13.7 4.3a2 2 0 0 0-3.4 0Z" /></svg>
				</span>
				<h3 class="dialog__title mb-1" id="bhpCancelConfirmLabel">Batalkan pengisian item?</h3>
				<p class="text-muted-foreground" id="bhpCancelConfirmDesc">Perubahan yang belum disimpan akan hilang.</p>
			</div>
			<div class="dialog__footer justify-center">
				<button type="button" class="button button--outline button--neutral" data-stisla-dialog-dismiss>Tetap di Halaman</button>
				<a class="button button--danger" href="<?= esc($cancelUrl, 'attr') ?>">Ya, Batalkan</a>
			</div>
		</div>
	</div>
</div>

<script>
(() => { const form = document.querySelector('#bhpItemForm'); const body = document.querySelector('#bhp-items tbody'); const template = body.querySelector('tr').cloneNode(true); const studyProgram = document.querySelector('input[name="study_program_id"]'); const mappings = <?= json_encode($studyProgramLaboratories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>; const openDialog = (dialog) => { if (!dialog) return; dialog.dataset.state = 'open'; dialog.setAttribute('aria-hidden', 'false'); window.requestAnimationFrame(() => { const focusTarget = dialog.querySelector('.button--primary, .button--danger, [data-stisla-dialog-dismiss]'); if (focusTarget) focusTarget.focus(); }); }; const closeDialog = (dialog) => { if (!dialog) return; dialog.dataset.state = 'closed'; dialog.setAttribute('aria-hidden', 'true'); }; const refreshLaboratories = () => { body.querySelectorAll('.item-laboratory').forEach((select) => { const selected = select.value || select.dataset.selected || ''; const options = mappings[studyProgram.value] || []; select.innerHTML = '<option value="">' + (options.length ? 'Pilih lab' : 'Tidak ada lab terdaftar') + '</option>'; options.forEach((lab) => { const option = document.createElement('option'); option.value = lab.id; option.textContent = lab.name; if (String(lab.id) === String(selected)) option.selected = true; select.add(option); }); select.disabled = options.length === 0; }); }; refreshLaboratories(); document.querySelector('#add-item').addEventListener('click', () => { const row = template.cloneNode(true); row.querySelectorAll('input').forEach((input) => { input.value = input.name === 'qty[]' ? '1' : ''; }); row.querySelector('.item-laboratory').value = ''; row.querySelector('.item-laboratory').dataset.selected = ''; body.appendChild(row); refreshLaboratories(); }); body.addEventListener('click', (event) => { if (event.target.matches('[data-remove]') && body.rows.length > 1) event.target.closest('tr').remove(); }); document.querySelector('#openBhpSaveConfirm').addEventListener('click', () => { if (form.reportValidity && !form.reportValidity()) return; openDialog(document.querySelector('#bhpSaveConfirm')); }); document.querySelector('#openBhpCancelConfirm').addEventListener('click', () => { openDialog(document.querySelector('#bhpCancelConfirm')); }); document.querySelector('#confirmBhpSave').addEventListener('click', (event) => { event.currentTarget.disabled = true; form.submit(); }); document.addEventListener('click', (event) => { if (!event.target.closest('[data-stisla-dialog-dismiss]')) return; closeDialog(event.target.closest('[data-stisla-dialog]')); }); })();
</script>
