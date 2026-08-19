<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Master Fakultas</span>
      <div class="card__action">
        <?php if (activeGroupCan('faculties.create')): ?>
        <a href="<?= base_url('admin/faculties/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Tambah Fakultas
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th class="text-center" style="width: 60px;">#</th><th>Kode</th><th>Nama Fakultas</th><th>Dekan</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (! empty($faculties)): ?>
              <?php $no = 1; foreach ($faculties as $faculty): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= esc($faculty['code']) ?></strong></td>
                <td><?= esc($faculty['name']) ?><?php if (! empty($faculty['description'])): ?><div class="text-xs text-muted-foreground"><?= esc($faculty['description']) ?></div><?php endif; ?></td>
                <td><?= esc($faculty['dean_name'] ?: '-') ?></td>
                <td><span class="badge badge--soft badge--<?= $faculty['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $faculty['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="text-center"><div class="flex justify-center gap-1">
                  <?php if (activeGroupCan('faculties.edit')): ?><a href="<?= base_url('admin/faculties/edit/' . $faculty['uuid']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                  <?php if (activeGroupCan('faculties.delete')): ?><form action="<?= base_url('admin/faculties/delete/' . $faculty['uuid']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus fakultas ini?')"><?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?>
                </div></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted-foreground py-8">Belum ada data fakultas.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>