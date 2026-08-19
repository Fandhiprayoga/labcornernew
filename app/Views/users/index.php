<div class="page__section">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Daftar User</span>
      <div class="card__action">
        <?php if (activeGroupCan('users.create')): ?>
        <a href="<?= base_url('admin/users/create') ?>" class="button button--primary button--sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M12 5v14m-7-7h14" />
          </svg>
          Tambah User
        </a>
        <?php endif; ?>
      </div>
    </div>
    <div class="card__body p-0">
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th class="text-center" style="width: 60px;">#</th>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($users)): ?>
              <?php $no = 1; foreach ($users as $user): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                  <div class="flex items-center gap-2">
                    <span class="avatar avatar--sm avatar--circle" data-stisla-avatar>
                      <span class="avatar__fallback"><?= esc(strtoupper(substr($user->username, 0, 2))) ?></span>
                    </span>
                    <?= esc($user->username) ?>
                  </div>
                </td>
                <td><?= esc($user->email) ?></td>
                <td>
                  <?php if (!empty($user->groups)): ?>
                    <?php foreach ($user->groups as $group): ?>
                      <?php
                        $badgeClass = match($group) {
                          'superadmin' => 'danger',
                          'kepala_lab' => 'warning',
                          'laboran'    => 'info',
                          'asisten_lab' => 'success',
                          'user'       => 'primary',
                          default      => 'primary',
                        };
                      ?>
                      <span class="badge badge--soft badge--<?= $badgeClass ?> mr-1"><?= ucfirst($group) ?></span>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <span class="badge badge--soft badge--secondary">No Role</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($user->active): ?>
                    <span class="badge badge--soft badge--success">Aktif</span>
                  <?php else: ?>
                    <span class="badge badge--soft badge--danger">Nonaktif</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="flex justify-center gap-1">
                    <?php if (activeGroupCan('users.edit')): ?>
                    <a href="<?= base_url('admin/users/edit/' . $user->id) ?>" class="button button--ghost button--neutral button--icon-only button--sm" title="Edit">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.475 5.408l2.117 2.117m-.756-3.482L12.109 9.77a2.1 2.1 0 0 0-.58 1.082L11 13l2.148-.53c.408-.1.787-.3 1.083-.579l5.727-5.727a1.85 1.85 0 1 0-2.617-2.617" />
                      </svg>
                    </a>
                    <?php endif; ?>
                    <?php if (activeGroupCan('users.delete') && $user->id !== auth()->id()): ?>
                    <form action="<?= base_url('admin/users/delete/' . $user->id) ?>" method="post" class="d-inline"
                          onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                      <?= csrf_field() ?>
                      <button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0l.5-2h11l.5 2" />
                        </svg>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="text-center text-muted-foreground py-8">Belum ada data user.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
