<?php
/** @var array $errors */
/** @var array|null $period */
/** @var string $mode */
$mode = $mode ?? 'create';
$period = $period ?? null;
$submitAction = $mode === 'edit' ? base_url('bhp/periods/update/' . ($period['id'] ?? 0)) : base_url('bhp/periods/store');
$startValue = old('tanggal_mulai') ?: ($period['tanggal_mulai'] ?? '');
$endValue = old('tanggal_selesai') ?: ($period['tanggal_selesai'] ?? '');
$namaValue = old('nama_periode') ?: ($period['nama_periode'] ?? '');
$startValue = $startValue ? date('Y-m-d\TH:i', strtotime($startValue)) : '';
$endValue = $endValue ? date('Y-m-d\TH:i', strtotime($endValue)) : '';
?>
<div class="page__section">
    <div class="card">
        <div class="card__header">
            <div>
                <span class="card__title"><?= $mode === 'edit' ? 'Edit Periode Pengajuan' : 'Buat Periode Pengajuan' ?></span>
                <div class="text-xs text-muted-foreground">Tentukan jendela waktu pengajuan BHP.</div>
            </div>
            <div class="card__action">
                <a class="button button--outline button--neutral button--sm" href="<?= base_url('bhp/periods') ?>">Kembali</a>
            </div>
        </div>
        <div class="card__body">
            <form method="post" action="<?= esc($submitAction) ?>" class="flex flex-col gap-4">
                <?= csrf_field() ?>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="field">
                        <label class="field__label" for="nama_periode">Nama Periode <span class="text-danger">*</span></label>
                        <input class="input w-full" id="nama_periode" name="nama_periode" value="<?= esc($namaValue) ?>" placeholder="Pengajuan BHP September 2026" required>
                    </div>
                    <div class="field">
                        <label class="field__label" for="tanggal_mulai">Tanggal Mulai <span class="text-danger">*</span></label>
                        <input class="input w-full" id="tanggal_mulai" type="datetime-local" name="tanggal_mulai" value="<?= esc($startValue) ?>" required>
                    </div>
                    <div class="field">
                        <label class="field__label" for="tanggal_selesai">Tanggal Selesai <span class="text-danger">*</span></label>
                        <input class="input w-full" id="tanggal_selesai" type="datetime-local" name="tanggal_selesai" value="<?= esc($endValue) ?>" required>
                    </div>
                </div>
                <div class="flex justify-end" style="border-top:1px solid var(--color-border);padding-top:1rem;">
                    <button class="button button--primary" style="min-width:7rem;height:2.75rem;padding:0 .875rem;display:inline-flex;align-items:center;justify-content:center;gap:.5rem;white-space:nowrap;" type="submit" title="Simpan periode">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.35em" height="1.35em" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M5 4h11l3 3v13H5z" opacity=".35" />
                            <path fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="1.5" d="M7 4v6h9V4m-9 9h10m-8 0v4h6v-4" />
                        </svg><span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
