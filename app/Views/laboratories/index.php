<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Master Laboratorium</span>
      <div class="card__action">
        <?php if (activeGroupCan('laboratories.create')): ?>
        <a href="<?= base_url('admin/laboratories/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Tambah Laboratorium
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th class="text-center" style="width: 60px;">#</th><th>Laboratorium</th><th>Ruangan</th><th>Program Studi</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (! empty($laboratories)): ?>
              <?php $no = 1; foreach ($laboratories as $laboratory): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= esc($laboratory['name']) ?><?php if (! empty($laboratory['description'])): ?><div class="text-xs text-muted-foreground"><?= esc($laboratory['description']) ?></div><?php endif; ?></td>
                <td><strong><?= esc($laboratory['room_code']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($laboratory['room_name']) ?></div></td>
                <td><?= esc($laboratory['study_programs'] ?: '-') ?></td>
                <td><span class="badge badge--soft badge--<?= $laboratory['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $laboratory['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="text-center"><div class="flex justify-center gap-1">
                  <?php if (activeGroupCan('laboratories.edit')): ?><a href="<?= base_url('admin/laboratories/edit/' . $laboratory['uuid']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                  <?php if (activeGroupCan('laboratories.delete')): ?><form action="<?= base_url('admin/laboratories/delete/' . $laboratory['uuid']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus laboratorium ini?')"><?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?>
                </div></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="6" class="text-center text-muted-foreground py-8">Belum ada data laboratorium.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>