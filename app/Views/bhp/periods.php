<?php

/** @var array $periods */
/** @var string $tab */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var int $perPage */
/** @var array $perPageOptions */
/** @var int $currentPage */
/** @var int $totalRows */ ?>
<div class="page__section flex flex-col gap-4">
    <div class="card">
        <div class="card__header"><span class="card__title">Daftar Periode</span><div class="card__action"><a class="button button--primary" href="<?= base_url('bhp/periods/create') ?>" title="Buat periode" aria-label="Buat periode"><svg xmlns="http://www.w3.org/2000/svg" width="1.35em" height="1.35em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg> Buat Periode</a></div></div>
        <div class="card__body">
            <form method="get" action="<?= base_url('bhp/periods') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;margin-bottom:1rem;">
                <input type="hidden" name="tab" value="<?= esc($tab) ?>">
                <div style="flex:0 0 110px;">
                    <label class="text-xs text-muted-foreground" for="perPage">Per Halaman</label>
                    <select class="select" id="perPage" name="perPage" onchange="this.form.submit()">
                        <?php foreach ($perPageOptions as $option): ?>
                        <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody><?php if (empty($periods)): ?><tr>
                                <td colspan="5"><?= view('partials/empty_table_state', ['message' => $tab === 'active' ? 'Belum ada periode aktif.' : 'Belum ada arsip periode.']) ?></td>
                            </tr><?php endif; ?><?php foreach ($periods as $period): $now = time(); $active = $now >= strtotime($period['tanggal_mulai']) && $now <= strtotime($period['tanggal_selesai']); $upcoming = $now < strtotime($period['tanggal_mulai']); ?><tr>
                                <td><?= esc($period['nama_periode']) ?></td>
                                <td><?= esc($period['tanggal_mulai']) ?></td>
                                <td><?= esc($period['tanggal_selesai']) ?></td>
                                <td><span class="badge badge--soft badge--<?= $active ? 'success' : ($upcoming ? 'info' : 'secondary') ?>"><?= $active ? 'Aktif' : ($upcoming ? 'Akan Aktif' : 'Tidak Aktif') ?></span></td>
                                <td class="text-end"><div class="flex justify-end gap-1"><a href="<?= base_url('bhp/periods/edit/' . $period['id']) ?>" class="button button--warning button--icon-only button--sm" title="Edit" aria-label="Edit periode"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" /></svg></a></div></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
        <?php if ($totalRows > 0): ?>
        <div class="card__body" style="border-top:1px solid var(--color-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;">
            <div class="text-xs text-muted-foreground">
                Menampilkan <?= $periods ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($periods) ?> dari <?= $totalRows ?> data
            </div>
            <?= $pager->only(['tab', 'perPage'])->links('default', 'app') ?>
        </div>
        <?php endif; ?>
    </div>
</div>