<?php

/** @var array $periods */
/** @var string $tab */ ?>
<div class="page__section flex flex-col gap-4">
    <div class="card">
        <div class="card__header"><span class="card__title">Daftar Periode</span><div class="card__action"><a class="button button--primary" href="<?= base_url('bhp/periods/create') ?>" title="Buat periode" aria-label="Buat periode"><svg xmlns="http://www.w3.org/2000/svg" width="1.35em" height="1.35em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg> Buat Periode</a></div></div>
        <div class="card__body">
            <nav aria-label="Filter periode" style="display:flex;gap:.25rem;overflow-x:auto;border-bottom:1px solid var(--color-border);margin-bottom:1rem;">
                <a href="<?= base_url('bhp/periods?tab=active') ?>" style="padding:.75rem 1rem;border-bottom:2px solid <?= $tab === 'active' ? 'var(--color-primary)' : 'transparent' ?>;color:<?= $tab === 'active' ? 'var(--color-foreground)' : 'var(--color-muted-foreground)' ?>;font-weight:<?= $tab === 'active' ? '600' : '400' ?>;white-space:nowrap;">Periode Aktif</a>
                <a href="<?= base_url('bhp/periods?tab=archive') ?>" style="padding:.75rem 1rem;border-bottom:2px solid <?= $tab === 'archive' ? 'var(--color-primary)' : 'transparent' ?>;color:<?= $tab === 'archive' ? 'var(--color-foreground)' : 'var(--color-muted-foreground)' ?>;font-weight:<?= $tab === 'archive' ? '600' : '400' ?>;white-space:nowrap;">Arsip Periode</a>
            </nav>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody><?php if (empty($periods)): ?><tr>
                                <td colspan="4"><?= view('partials/empty_table_state', ['message' => $tab === 'active' ? 'Belum ada periode aktif.' : 'Belum ada arsip periode.']) ?></td>
                            </tr><?php endif; ?><?php foreach ($periods as $period): $active = time() >= strtotime($period['tanggal_mulai']) && time() <= strtotime($period['tanggal_selesai']); ?><tr>
                                <td><?= esc($period['nama_periode']) ?></td>
                                <td><?= esc($period['tanggal_mulai']) ?></td>
                                <td><?= esc($period['tanggal_selesai']) ?></td>
                                <td><span class="badge badge--soft badge--<?= $active ? 'success' : 'secondary' ?>"><?= $active ? 'Aktif' : 'Tidak Aktif' ?></span></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
    </div>
</div>