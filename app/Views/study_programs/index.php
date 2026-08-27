<?php
/** @var array $studyPrograms */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var array $faculties */
/** @var array $degrees */
/** @var string $search */
/** @var int $facultyId */
/** @var string $degree */
/** @var string $status */
/** @var int $perPage */
/** @var array $perPageOptions */
/** @var int $currentPage */
/** @var int $totalRows */
?>
<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Manajemen Program Studi</span>
      <div class="card__action">
        <?php if (activeGroupCan('study-programs.create')): ?>
        <a href="<?= base_url('admin/study-programs/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" /></svg>
          Tambah Program Studi
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body" style="border-bottom: 1px solid var(--color-border);">
      <form method="get" action="<?= base_url('admin/study-programs') ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
        <div style="flex:1 1 260px;min-width:220px;">
          <label class="text-xs text-muted-foreground" for="q">Cari</label>
          <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Kode, nama program studi, atau fakultas...">
        </div>
        <div style="flex:0 1 240px;min-width:200px;">
          <label class="text-xs text-muted-foreground" for="faculty_id">Fakultas</label>
          <select class="select" id="faculty_id" name="faculty_id">
            <option value="">Semua Fakultas</option>
            <?php foreach ($faculties as $faculty): ?>
            <option value="<?= $faculty['id'] ?>" <?= $facultyId === (int) $faculty['id'] ? 'selected' : '' ?>><?= esc($faculty['code'] . ' - ' . $faculty['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div style="flex:0 1 150px;min-width:130px;">
          <label class="text-xs text-muted-foreground" for="degree">Jenjang</label>
          <select class="select" id="degree" name="degree">
            <option value="">Semua Jenjang</option>
            <?php foreach ($degrees as $option): ?>
            <option value="<?= esc($option) ?>" <?= $degree === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
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
          <?php if ($search !== '' || $facultyId > 0 || $degree !== '' || $status !== ''): ?>
          <a href="<?= base_url('admin/study-programs') ?>" class="button button--outline button--sm">Reset</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead><tr><th class="text-center" style="width: 60px;">#</th><th>Kode</th><th>Program Studi</th><th>Fakultas</th><th>Jenjang</th><th>Status</th><th class="text-center">Aksi</th></tr></thead>
          <tbody>
            <?php if (! empty($studyPrograms)): ?>
              <?php $no = (($currentPage - 1) * $perPage) + 1; foreach ($studyPrograms as $studyProgram): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= esc($studyProgram['code']) ?></strong></td>
                <td><?= esc($studyProgram['name']) ?><?php if (! empty($studyProgram['description'])): ?><div class="text-xs text-muted-foreground"><?= esc($studyProgram['description']) ?></div><?php endif; ?></td>
                <td><strong><?= esc($studyProgram['faculty_code']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($studyProgram['faculty_name']) ?></div></td>
                <td><?= esc($studyProgram['degree']) ?></td>
                <td><span class="badge badge--soft badge--<?= $studyProgram['status'] === 'active' ? 'success' : 'secondary' ?>"><?= $studyProgram['status'] === 'active' ? 'Aktif' : 'Nonaktif' ?></span></td>
                <td class="text-center"><div class="flex justify-center gap-1">
                  <?php if (activeGroupCan('study-programs.edit')): ?><a href="<?= base_url('admin/study-programs/edit/' . $studyProgram['uuid']) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408 2.117 2.117m-.756-3.482-5.727 5.727a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" /></svg></a><?php endif; ?>
                  <?php if (activeGroupCan('study-programs.delete')): ?><form action="<?= base_url('admin/study-programs/delete/' . $studyProgram['uuid']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus program studi ini?')"><?= csrf_field() ?><button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg></button></form><?php endif; ?>
                </div></td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => ($search !== '' || $facultyId > 0 || $degree !== '' || $status !== '') ? 'Data tidak ditemukan untuk filter tersebut.' : 'Belum ada data program studi.']) ?></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php if ($totalRows > 0): ?>
    <div class="card__body" style="border-top: 1px solid var(--color-border); display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.75rem;">
      <div class="text-xs text-muted-foreground">
        Menampilkan <?= $studyPrograms ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($studyPrograms) ?> dari <?= $totalRows ?> data
      </div>
      <?= $pager->only(['q', 'faculty_id', 'degree', 'status', 'perPage'])->links('default', 'app') ?>
    </div>
    <?php endif; ?>
  </div>
</div>