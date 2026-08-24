<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Master Laboran</span>
      <div class="card__action">
        <?php if (activeGroupCan('laboratory-laborans.create')): ?>
        <a href="<?= base_url('admin/laboratory-laborans/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Tambah Penugasan
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body" style="border-bottom: 1px solid var(--color-border);">
      <form method="get" action="<?= base_url('admin/laboratory-laborans') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 260px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Nama laboran, email, lab, atau ruangan...">
        </div>
        <div style="flex:0 1 240px;min-width:200px;">
          <label class="text-xs text-muted-foreground" for="laboratory_id">Laboratorium</label>
          <select class="select" id="laboratory_id" name="laboratory_id">
            <option value="">Semua Laboratorium</option>
            <?php foreach ($laboratoryOptions as $option): ?>
            <option value="<?= $option['id'] ?>" <?= $laboratoryId === (int) $option['id'] ? 'selected' : '' ?>>
              <?= esc($option['name']) ?><?= $option['room_code'] ? ' (' . esc($option['room_code']) . ')' : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:0 0 110px;">
          <label class="text-xs text-muted-foreground" for="perPage">Per Halaman</label>
          <select class="select" id="perPage" name="perPage">
            <?php foreach ($perPageOptions as $option): ?>
            <option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="display:flex;gap:.5rem;">
          <button type="submit" class="button button--primary button--sm">Filter</button>
          <?php if ($search !== '' || $laboratoryId > 0): ?>
          <a href="<?= base_url('admin/laboratory-laborans') ?>" class="button button--outline button--sm">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th class="text-center" style="width: 60px;">#</th><th>Laboran</th><th>Laboratorium</th><th>Ruangan</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (! empty($assignments)): ?>
              <?php $no = (($currentPage - 1) * $perPage) + 1; foreach ($assignments as $assignment): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= esc($assignment['username']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($assignment['email'] ?: '-') ?></div></td>
                <td><?= esc($assignment['laboratory_name']) ?></td>
                <td><strong><?= esc($assignment['room_code'] ?: '-') ?></strong><div class="text-xs text-muted-foreground"><?= esc($assignment['room_name'] ?: '-') ?></div></td>
                <td class="text-center"><div class="flex justify-center gap-1">
                  <?php if (activeGroupCan('laboratory-laborans.edit')): ?><a href="<?= base_url('admin/laboratory-laborans/edit/' . $assignment['id']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                  <?php if (activeGroupCan('laboratory-laborans.delete')): ?><form action="<?= base_url('admin/laboratory-laborans/delete/' . $assignment['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus penugasan laboran ini?')"><?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?>
                </div></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="5" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => ($search !== '' || $laboratoryId > 0) ? 'Data tidak ditemukan untuk filter tersebut.' : 'Belum ada penugasan laboran.']) ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalRows > 0): ?>
    <div class="card__body" style="border-top: 1px solid var(--color-border); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem;">
      <div class="text-xs text-muted-foreground">
        Menampilkan <?= $assignments ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($assignments) ?> dari <?= $totalRows ?> data
      </div>
      <?= $pager->only(['q', 'laboratory_id', 'perPage'])->links('default', 'app') ?>
    </div>
    <?php endif; ?>
  </div>
</div>