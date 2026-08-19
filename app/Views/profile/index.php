<?php $currentUser = auth()->user(); ?>

<div class="page__section">
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 lg:col-span-4">
      <div class="card text-center h-full">
        <div class="card__body h-full flex flex-col items-center justify-center">
          <span class="avatar avatar--xl avatar--circle mx-auto mb-3" data-stisla-avatar>
            <span class="avatar__fallback" style="font-size: 2rem;"><?= esc(strtoupper(substr($currentUser->username, 0, 2))) ?></span>
          </span>
          <h5 class="text-lg font-semibold"><?= esc($currentUser->username) ?></h5>
          <p class="text-muted-foreground"><?= esc($currentUser->email) ?></p>
          <div class="flex justify-center gap-1 mt-2">
            <?php foreach ($userGroups as $group): ?>
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
              <span class="badge badge--soft badge--<?= $badgeClass ?>"><?= ucfirst($group) ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-12 lg:col-span-8">
      <div class="card h-full">
        <div class="card__header">
          <span class="card__title">Edit Profil</span>
        </div>
        <div class="card__body">
          <form action="<?= base_url('profile/update') ?>" method="post" class="flex flex-col gap-4">
            <?= csrf_field() ?>

            <div class="field">
              <label for="username" class="field__label">Username</label>
              <div class="input-group">
                <input type="text" class="input" id="username" name="username"
                       value="<?= old('username', $currentUser->username) ?>" required>
              </div>
            </div>

            <div class="field">
              <label for="email" class="field__label">Email</label>
              <div class="input-group">
                <input type="email" class="input" id="email" value="<?= esc($currentUser->email) ?>" disabled>
              </div>
              <small class="text-muted-foreground text-xs">Email tidak dapat diubah.</small>
            </div>

            <div class="field">
              <label for="password" class="field__label">Password Baru</label>
              <div class="input-group">
                <input type="password" class="input" id="password" name="password" autocomplete="new-password" autocapitalize="off" spellcheck="false">
              </div>
              <small class="text-muted-foreground text-xs">Kosongkan jika tidak ingin mengubah password.</small>
            </div>

            <div class="flex justify-end pt-2">
              <button type="submit" class="button button--primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                  <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M17 21H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4h6l7 7v7a4 4 0 0 1-4 4z" />
                  <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M13 3v4a2 2 0 0 0 2 2h4" />
                </svg>
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
