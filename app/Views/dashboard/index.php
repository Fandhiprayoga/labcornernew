<?php
$currentUser = auth()->user();
$groups = $currentUser->getGroups();
$groupLabel = activeGroupTitle();
?>

<div class="page__section">
  <div class="mb-4">
    <h2 class="text-xl font-semibold">Selamat Datang, <?= esc($currentUser->username) ?>!</h2>
    <p class="text-muted-foreground mt-1">
      Anda login sebagai <span class="font-medium"><?= $groupLabel ?></span>.
      <?php if (count($groups) > 1): ?>
        <small class="text-muted-foreground">(Memiliki <?= count($groups) ?> role. Gunakan switcher di navbar untuk beralih.)</small>
      <?php endif; ?>
    </p>
  </div>
</div>

<?php if (activeGroupCan('admin.access')): ?>
<div class="page__section">
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--primary icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="6" r="4" />
                  <path d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5Z" />
                </g>
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value"><?php $userModel = new \CodeIgniter\Shield\Models\UserModel(); echo $userModel->countAllResults(); ?></div>
            <div class="stat__label text-eyebrow">Total Users</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--danger icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M2 16c0-2.828 0-4.243.879-5.121C3.757 10 5.172 10 8 10h8c2.828 0 4.243 0 5.121.879C22 11.757 22 13.172 22 16s0 4.243-.879 5.121C20.243 22 18.828 22 16 22H8c-2.828 0-4.243 0-5.121-.879C2 20.243 2 18.828 2 16" opacity=".5" />
                <path fill="currentColor" d="M12 18a2 2 0 1 0 0-4a2 2 0 0 0 0 4M6.75 8a5.25 5.25 0 0 1 10.5 0v2.004c.567.005 1.064.018 1.5.05V8a6.75 6.75 0 0 0-13.5 0v2.055a24 24 0 0 1 1.5-.051z" />
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value"><?= count(config('AuthGroups')->groups) ?></div>
            <div class="stat__label text-eyebrow">Total Roles</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--warning icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M14.279 2.152C13.909 2 13.439 2 12.5 2s-1.408 0-1.779.152a2 2 0 0 0-1.09 1.083c-.094.223-.13.484-.145.863" opacity=".5" />
                <path fill="currentColor" d="M15.523 12c0 1.657-1.354 3-3.023 3s-3.023-1.343-3.023-3S10.83 9 12.5 9s3.023 1.343 3.023 3" />
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value"><?= count(config('AuthGroups')->permissions) ?></div>
            <div class="stat__label text-eyebrow">Total Permissions</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
      <div class="card card--stat">
        <div class="card__body">
          <div class="flex justify-between items-center">
            <span class="icon-box icon-box--success icon-box--lg">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m5 13l4 4L19 7" />
              </svg>
            </span>
          </div>
          <div class="stat mt-3">
            <div class="stat__value">Active</div>
            <div class="stat__label text-eyebrow">Status Sistem</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="page__section">
  <div class="grid grid-cols-12 gap-4">
    <div class="col-span-12 lg:col-span-6">
      <div class="card">
        <div class="card__header">
          <span class="card__title">Informasi Akun</span>
        </div>
        <div class="card__body">
          <table class="table">
            <tbody>
              <tr>
                <th class="text-muted-foreground font-medium" style="width: 140px;">Username</th>
                <td><?= esc($currentUser->username) ?></td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Email</th>
                <td><?= esc($currentUser->email) ?></td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Role</th>
                <td>
                  <?php foreach ($groups as $grp): ?>
                    <span class="badge badge--soft badge--primary mr-1"><?= esc(ucfirst($grp)) ?></span>
                  <?php endforeach; ?>
                </td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Program Studi</th>
                <td>
                  <?php if ($currentStudyProgram): ?>
                    <?= esc($currentStudyProgram['name']) ?>
                  <?php else: ?>
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="badge badge--soft badge--warning">Belum diisi</span>
                      <a href="<?= site_url('profile') ?>" class="button button--primary button--icon-only button--sm" title="Isi Program Studi" aria-label="Isi Program Studi">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.5 4.5a2.121 2.121 0 0 1 3 3L7 21l-4 1l1-4Z" />
                        </svg>
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <th class="text-muted-foreground font-medium">Nomor Telepon</th>
                <td>
                  <?php if (! empty($currentUser->phone)): ?>
                    <?= esc($currentUser->phone) ?>
                  <?php else: ?>
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="badge badge--soft badge--warning">Belum diisi</span>
                      <a href="<?= site_url('profile') ?>" class="button button--primary button--icon-only button--sm" title="Isi Nomor Telepon" aria-label="Isi Nomor Telepon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.5 4.5a2.121 2.121 0 0 1 3 3L7 21l-4 1l1-4Z" />
                        </svg>
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
