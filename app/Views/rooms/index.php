<?php
/** @var array $rooms */
/** @var array $types */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var string $search */
/** @var string $type */
/** @var string $status */
/** @var int $perPage */
/** @var array $perPageOptions */
/** @var int $currentPage */
/** @var int $totalRows */
?>
<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Master Ruangan</span>
      <div class="card__action">
        <?php if (activeGroupCan('rooms.create')): ?>
        <a href="<?= base_url('admin/rooms/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Tambah Ruangan
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body" style="border-bottom: 1px solid var(--color-border);">
      <form method="get" action="<?= base_url('admin/rooms') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 260px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Kode, nama, atau gedung...">
        </div>
        <div style="flex:0 1 190px;min-width:170px;">
          <label class="text-xs text-muted-foreground" for="type">Jenis</label>
          <select class="select" id="type" name="type">
            <option value="">Semua Jenis</option>
            <?php foreach ($types as $option): ?>
            <option value="<?= esc($option) ?>" <?= $type === $option ? 'selected' : '' ?>><?= esc(ucfirst($option)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:0 1 160px;min-width:150px;">
          <label class="text-xs text-muted-foreground" for="status">Status</label>
          <select class="select" id="status" name="status">
            <option value="">Semua Status</option>
            <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
            <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
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
          <?php if ($search !== '' || $type !== '' || $status !== ''): ?>
          <a href="<?= base_url('admin/rooms') ?>" class="button button--outline button--sm">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th class="text-center" style="width: 60px;">#</th>
              <th>Kode</th>
              <th>Nama Ruangan</th>
              <th>Lokasi</th>
              <th class="text-center">Kapasitas</th>
              <th>Jenis</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (! empty($rooms)): ?>
              <?php $no = (($currentPage - 1) * $perPage) + 1; foreach ($rooms as $room): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= esc($room['code']) ?></strong></td>
                <td>
                  <?= esc($room['name']) ?>
                  <?php if (! empty($room['description'])): ?><div class="text-xs text-muted-foreground"><?= esc($room['description']) ?></div><?php endif; ?>
                </td>
                <td><?= esc($room['building'] ?: '-') ?><?= $room['floor'] !== null ? ' · Lantai ' . esc($room['floor']) : '' ?></td>
                <td class="text-center"><?= esc($room['capacity']) ?> orang</td>
                <td><?= esc(ucfirst($room['type'])) ?></td>
                <td><span class="badge badge--soft badge--<?= $room['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $room['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="text-center">
                  <div class="flex justify-center gap-1">
                    <?php if (activeGroupCan('rooms.edit')): ?><a href="<?= base_url('admin/rooms/edit/' . $room['id']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                    <?php if (activeGroupCan('rooms.delete')): ?>
                    <form action="<?= base_url('admin/rooms/delete/' . $room['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus ruangan ini?')">
                      <?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="8" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => ($search !== '' || $type !== '' || $status !== '') ? 'Data tidak ditemukan untuk filter tersebut.' : 'Belum ada data ruangan.']) ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalRows > 0): ?>
    <div class="card__body" style="border-top: 1px solid var(--color-border); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem;">
      <div class="text-xs text-muted-foreground">
        Menampilkan <?= $rooms ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($rooms) ?> dari <?= $totalRows ?> data
      </div>
      <?= $pager->only(['q', 'type', 'status', 'perPage'])->links('default', 'app') ?>
    </div>
    <?php endif; ?>
  </div>
</div>