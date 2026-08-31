<?php
/** @var array $laboratories */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var array $rooms */
/** @var array $studyPrograms */
/** @var string $search */
/** @var int $roomId */
/** @var int $studyProgramId */
/** @var string $status */
/** @var int $perPage */
/** @var array $perPageOptions */
/** @var int $currentPage */
/** @var int $totalRows */
?>
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
    <div class="card__body" style="border-bottom: 1px solid var(--color-border);">
      <form method="get" action="<?= base_url('admin/laboratories') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 260px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Nama laboratorium, ruangan, atau program studi...">
        </div>
        <div style="flex:0 1 220px;min-width:190px;">
          <label class="text-xs text-muted-foreground" for="room_id">Ruangan</label>
          <select class="select" id="room_id" name="room_id">
            <option value="">Semua Ruangan</option>
            <?php foreach ($rooms as $room): ?>
            <option value="<?= $room['id'] ?>" <?= $roomId === (int) $room['id'] ? 'selected' : '' ?>><?= esc($room['code'] . ' - ' . $room['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:0 1 240px;min-width:200px;">
          <label class="text-xs text-muted-foreground" for="study_program_id">Program Studi</label>
          <select class="select" id="study_program_id" name="study_program_id">
            <option value="">Semua Program Studi</option>
            <?php foreach ($studyPrograms as $studyProgram): ?>
            <option value="<?= $studyProgram['id'] ?>" <?= $studyProgramId === (int) $studyProgram['id'] ? 'selected' : '' ?>><?= esc($studyProgram['degree'] . ' ' . $studyProgram['code'] . ' - ' . $studyProgram['name']) ?></option>
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
          <?php if ($search !== '' || $roomId > 0 || $studyProgramId > 0 || $status !== ''): ?>
          <a href="<?= base_url('admin/laboratories') ?>" class="button button--outline button--sm">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th class="text-center" style="width: 60px;">#</th><th class="text-center" style="width: 72px;">Foto</th><th>Laboratorium</th><th>Ruangan</th><th>Program Studi</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (! empty($laboratories)): ?>
              <?php $no = (($currentPage - 1) * $perPage) + 1; foreach ($laboratories as $laboratory): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center"><img src="<?= base_url($laboratory['photo'] ?: 'assets/images/default-laboratory.svg') ?>" alt="Foto <?= esc($laboratory['name']) ?>" width="48" height="48" style="object-fit:cover;border-radius:8px;"></td>
                <td><?= esc($laboratory['name']) ?><?php if (! empty($laboratory['description'])): ?><div class="text-xs text-muted-foreground"><?= esc($laboratory['description']) ?></div><?php endif; ?></td>
                <td><strong><?= esc($laboratory['room_code']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($laboratory['room_name']) ?></div></td>
                <td><?php if (! empty($laboratory['study_programs'])): ?><div class="flex flex-wrap gap-1"><?php foreach (explode(', ', $laboratory['study_programs']) as $studyProgram): ?><span class="badge badge--soft badge--secondary"><?= esc($studyProgram) ?></span><?php endforeach; ?></div><?php else: ?>-<?php endif; ?></td>
                <td><span class="badge badge--soft badge--<?= $laboratory['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $laboratory['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="text-center"><div class="flex justify-center gap-1">
                  <?php if (activeGroupCan('laboratories.edit')): ?><a href="<?= base_url('admin/laboratories/edit/' . $laboratory['uuid']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                  <?php if (activeGroupCan('laboratories.delete')): ?><form action="<?= base_url('admin/laboratories/delete/' . $laboratory['uuid']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus laboratorium ini?')"><?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?>
                </div></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => ($search !== '' || $roomId > 0 || $studyProgramId > 0 || $status !== '') ? 'Data tidak ditemukan untuk filter tersebut.' : 'Belum ada data laboratorium.']) ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalRows > 0): ?>
    <div class="card__body" style="border-top: 1px solid var(--color-border); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem;">
      <div class="text-xs text-muted-foreground">
        Menampilkan <?= $laboratories ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($laboratories) ?> dari <?= $totalRows ?> data
      </div>
      <?= $pager->only(['q', 'room_id', 'study_program_id', 'status', 'perPage'])->links('default', 'app') ?>
    </div>
    <?php endif; ?>
  </div>
</div>