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
              <th class="text-end">Aksi</th>
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
                <td class="text-end">
                  <div class="flex justify-end gap-1">
                    <?php if (activeGroupCan('users.edit')): ?>
                    <a href="<?= base_url('admin/users/edit/' . $user->id) ?>" class="button button--warning button--icon-only button--sm" title="Edit">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m16.5 4.5 3 3L8 19H5v-3L16.5 4.5Z" />
                      </svg>
                    </a>
                    <?php endif; ?>
                      <?php if (activeGroupCan('users.delete') && $user->id !== auth()->id()): ?>
                      <form action="<?= base_url('admin/users/delete/' . $user->id) ?>" method="post" class="d-inline"
                        data-user-delete-form data-user-name="<?= esc($user->username, 'attr') ?>">
                      <?= csrf_field() ?>
                      <button type="submit" class="button button--danger button--icon-only button--sm" title="Hapus">
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
                <td colspan="6" class="text-center text-muted-foreground py-8"><?= view('partials/empty_table_state', ['message' => 'Belum ada data user.']) ?></td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="dialog dialog--sm" id="userDeleteConfirm" data-state="closed" role="alertdialog" aria-modal="true" aria-labelledby="userDeleteConfirmLabel" aria-describedby="userDeleteConfirmDesc" aria-hidden="true" tabindex="-1">
  <div class="dialog__backdrop" data-user-dialog-dismiss></div>
  <div class="dialog__panel">
    <div class="dialog__content">
      <button type="button" class="dialog__close" data-user-dialog-dismiss aria-label="Tutup"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" /></svg></button>
      <div class="dialog__body text-center pt-6">
        <span class="icon-box icon-box--danger icon-box--circle icon-box--lg mb-3"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 7h12m-9 0v10m6-10v10M8 7l.75-2h6.5L16 7m-9 0 .75 13.5h6.5L15 7" /></svg></span>
        <h3 class="dialog__title mb-1" id="userDeleteConfirmLabel">Hapus user?</h3>
        <p class="text-muted-foreground" id="userDeleteConfirmDesc">User <strong id="userDeleteConfirmName"></strong> akan dihapus.</p>
      </div>
      <form id="userDeleteForm" method="post">
        <?= csrf_field() ?>
        <div class="dialog__footer justify-center">
          <button type="button" class="button button--outline button--neutral" data-user-dialog-dismiss>Batal</button>
          <button type="submit" class="button button--danger">Ya, Hapus</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var dialog = document.getElementById('userDeleteConfirm');
    var modalForm = document.getElementById('userDeleteForm');
    var name = document.getElementById('userDeleteConfirmName');

    document.querySelectorAll('[data-user-delete-form]').forEach(function (form) {
      form.addEventListener('submit', function (event) {
        event.preventDefault();
        modalForm.action = form.action;
        name.textContent = form.dataset.userName || 'ini';
        dialog.dataset.state = 'open';
        dialog.setAttribute('aria-hidden', 'false');
        window.requestAnimationFrame(function () { modalForm.querySelector('[data-user-dialog-dismiss]').focus(); });
      });
    });

    dialog.querySelectorAll('[data-user-dialog-dismiss]').forEach(function (element) {
      element.addEventListener('click', function () {
        dialog.dataset.state = 'closed';
        dialog.setAttribute('aria-hidden', 'true');
      });
    });

    modalForm.addEventListener('submit', function () {
      var submitButton = this.querySelector('button[type="submit"]');
      if (submitButton) submitButton.disabled = true;
    });
  });
</script>
