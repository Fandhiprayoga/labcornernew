<?php
$currentUser  = auth()->user();
$userGroups   = $currentUser->getGroups();
$active       = activeGroup();
$authGroups   = config('AuthGroups');
$navNotifications = notification()->getForUser($currentUser->id, 5);
$navUnreadCount    = notification()->unreadCount($currentUser->id);
$badgeColors = [
    'superadmin' => 'danger',
    'kepala_lab' => 'warning',
    'laboran'    => 'info',
    'asisten_lab' => 'success',
    'user'       => 'primary',
];
?>
<header class="navbar">
  <button
    type="button"
    class="button button--ghost button--neutral button--icon-only button--flush-start"
    data-stisla-app-shell-toggle="auto"
    aria-label="Toggle sidebar"
  >
    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
      <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 7H4m16 5H4m16 5H4" />
    </svg>
  </button>

  <div class="input-group input-group--search hidden lg:flex">
    <span class="input-group__text">
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
        <g fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="11.5" cy="11.5" r="9.5" />
          <path stroke-linecap="round" d="M18.5 18.5L22 22" />
        </g>
      </svg>
    </span>
    <input type="search" class="input" placeholder="Search..." aria-label="Search" />
  </div>

  <div class="ms-auto">
    <div class="flex gap-1 items-center">

      <!-- Group Switcher -->
      <?php if (count($userGroups) > 1): ?>
      <div class="menu">
        <button
          type="button"
          class="button button--ghost button--neutral flex items-center gap-2"
          data-stisla-menu-trigger="groupSwitcher"
          aria-haspopup="menu"
          aria-expanded="false"
          aria-controls="groupSwitcher"
        >
          <span class="badge badge--<?= $badgeColors[$active] ?? 'secondary' ?>">
            <?= esc($authGroups->groups[$active]['title'] ?? ucfirst($active)) ?>
          </span>
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m19 9l-7 6l-7-6" />
          </svg>
        </button>
        <div class="menu__popup w-48" id="groupSwitcher" data-stisla-menu role="menu" data-state="closed">
          <div class="menu__group" role="group">
            <h3 class="menu__group-label">Switch Role</h3>
            <?php foreach ($userGroups as $grp): ?>
              <?php if ($grp === $active): ?>
                <span class="menu__item active disabled">
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13l4 4L19 7" />
                  </svg>
                  <?= esc($authGroups->groups[$grp]['title'] ?? ucfirst($grp)) ?>
                </span>
              <?php else: ?>
                <form action="<?= base_url('switch-group') ?>" method="post" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="group" value="<?= $grp ?>">
                  <button type="submit" class="menu__item w-full text-start" role="menuitem">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                      <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="1.5" />
                    </svg>
                    <?= esc($authGroups->groups[$grp]['title'] ?? ucfirst($grp)) ?>
                  </button>
                </form>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Theme Toggle -->
      <button
        type="button"
        class="button button--ghost button--neutral button--icon-only"
        data-theme-toggle
        aria-label="Toggle theme"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="currentColor" d="m21.067 11.857l-.642-.388zm-8.924-8.924l-.388-.642zM21.25 12A9.25 9.25 0 0 1 12 21.25v1.5c5.937 0 10.75-4.813 10.75-10.75zM12 21.25A9.25 9.25 0 0 1 2.75 12h-1.5c0 5.937 4.813 10.75 10.75 10.75zM2.75 12A9.25 9.25 0 0 1 12 2.75v-1.5C6.063 1.25 1.25 6.063 1.25 12zm12.75 2.25A5.75 5.75 0 0 1 9.75 8.5h-1.5a7.25 7.25 0 0 0 7.25 7.25zm4.925-2.781A5.75 5.75 0 0 1 15.5 14.25v1.5a7.25 7.25 0 0 0 6.21-3.505zM9.75 8.5a5.75 5.75 0 0 1 2.781-4.925l-.776-1.284A7.25 7.25 0 0 0 8.25 8.5zM12 2.75a.38.38 0 0 1-.268-.118a.3.3 0 0 1-.082-.155c-.004-.031-.002-.121.105-.186l.776 1.284c.503-.304.665-.861.606-1.299c-.062-.455-.42-1.026-1.137-1.026zm9.71 9.495c-.066.107-.156.109-.187.105a.3.3 0 0 1-.155-.082a.38.38 0 0 1-.118-.268h1.5c0-.717-.571-1.075-1.026-1.137c-.438-.059-.995.103-1.299.606z" />
        </svg>
      </button>

      <!-- Notification Bell -->
      <div class="menu">
        <button
          type="button"
          class="button button--ghost button--neutral button--icon-only"
          data-stisla-menu-trigger="topbarNotifications"
          aria-haspopup="menu"
          aria-expanded="false"
          aria-controls="topbarNotifications"
          aria-label="Notifikasi"
          style="position: relative;"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M3.833 8.406c.208-3.696 3.164-6.652 6.86-6.86a26 26 0 0 1 2.614 0c3.696.208 6.652 3.164 6.86 6.86c.045.806.045 1.622 0 2.428a10 10 0 0 0 1.535 5.474l.238.376c.945 1.49.223 3.482-1.462 3.996a30.7 30.7 0 0 1-17.956 0c-1.685-.514-2.407-2.506-1.462-3.996l.238-.376a10 10 0 0 0 1.535-5.474a25 25 0 0 1 0-2.428" opacity=".5" />
            <path fill="currentColor" d="M7.828 20.24a4.185 4.185 0 0 0 8.344 0c-2.767.32-5.577.32-8.344 0" />
          </svg>
          <?php if ($navUnreadCount > 0): ?>
            <span class="badge badge--danger" style="position: absolute; top: 2px; right: 2px; font-size: .6rem; padding: 0 4px;"><?= $navUnreadCount > 9 ? '9+' : $navUnreadCount ?></span>
          <?php endif; ?>
        </button>
        <div class="menu__popup w-64" id="topbarNotifications" data-stisla-menu role="menu" data-state="closed">
          <div class="menu__group" role="group">
            <h3 class="menu__group-label">Notifikasi</h3>
            <?php if (empty($navNotifications)): ?>
              <span class="menu__item disabled">Belum ada notifikasi.</span>
            <?php else: ?>
              <?php foreach ($navNotifications as $notif): ?>
                <a href="<?= base_url('notifications/read/' . $notif['id']) ?>" class="menu__item flex-col items-start" role="menuitem" style="white-space: normal;">
                  <span class="flex items-center gap-2">
                    <?php if (! $notif['is_read']): ?><span class="badge badge--danger" style="width:6px;height:6px;padding:0;border-radius:50%;"></span><?php endif; ?>
                    <span class="font-medium"><?= esc($notif['title']) ?></span>
                  </span>
                  <?php if (! empty($notif['message'])): ?>
                    <span class="text-muted-foreground text-xs"><?= esc($notif['message']) ?></span>
                  <?php endif; ?>
                </a>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <hr class="menu__separator" role="separator" />
          <a href="<?= base_url('notifications') ?>" class="menu__item justify-center" role="menuitem">Lihat Semua Notifikasi</a>
        </div>
      </div>

      <!-- User Menu -->
      <div class="menu">
        <button
          type="button"
          class="button button--ghost button--neutral flex items-center gap-2"
          data-stisla-menu-trigger="topbarUser"
          aria-haspopup="menu"
          aria-expanded="false"
          aria-controls="topbarUser"
        >
          <span class="hidden sm:inline font-medium"><?= esc($currentUser->username ?? 'User') ?></span>
          <span class="avatar avatar--sm avatar--circle" data-stisla-avatar>
            <span class="avatar__fallback"><?= esc(strtoupper(substr($currentUser->username ?? 'U', 0, 2))) ?></span>
          </span>
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m19 9l-7 6l-7-6" />
          </svg>
        </button>
        <div class="menu__popup w-48" id="topbarUser" data-stisla-menu role="menu" data-state="closed">
          <div class="menu__group" role="group">
            <h3 class="menu__group-label"><?= esc($currentUser->username ?? 'User') ?></h3>
            <?php if (count($userGroups) === 1): ?>
            <span class="menu__item disabled">
              <span class="badge badge--<?= $badgeColors[$active] ?? 'secondary' ?>"><?= esc(activeGroupTitle()) ?></span>
            </span>
            <?php endif; ?>
            <a href="<?= base_url('profile') ?>" class="menu__item" role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="6" r="4" />
                  <path d="M20 17.5c0 2.485 0 4.5-8 4.5s-8-2.015-8-4.5S7.582 13 12 13s8 2.015 8 4.5Z" />
                </g>
              </svg>
              Profil
            </a>
            <?php if (activeGroupCan('admin.settings')): ?>
            <a href="<?= base_url('admin/settings') ?>" class="menu__item" role="menuitem">
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <g fill="none" stroke="currentColor" stroke-width="1.5">
                  <circle cx="12" cy="12" r="3" />
                  <path d="M13.765 2.152C13.398 2 12.932 2 12 2s-1.398 0-1.765.152a2 2 0 0 0-1.083 1.083c-.092.223-.129.484-.143.863a1.62 1.62 0 0 1-.79 1.353a1.62 1.62 0 0 1-1.567.008c-.336-.178-.579-.276-.82-.308a2 2 0 0 0-1.478.396C4.04 5.79 3.806 6.193 3.34 7s-.7 1.21-.751 1.605a2 2 0 0 0 .396 1.479c.148.192.355.353.676.555c.473.297.777.803.777 1.361s-.304 1.064-.777 1.36c-.321.203-.529.364-.676.556a2 2 0 0 0-.396 1.479c.052.394.285.798.75 1.605c.467.807.7 1.21 1.015 1.453a2 2 0 0 0 1.479.396c.24-.032.483-.13.819-.308a1.62 1.62 0 0 1 1.567.008c.483.28.77.795.79 1.353c.014.38.05.64.143.863a2 2 0 0 0 1.083 1.083C10.602 22 11.068 22 12 22s1.398 0 1.765-.152a2 2 0 0 0 1.083-1.083c.092-.223.129-.483.143-.863c.02-.558.307-1.074.79-1.353a1.62 1.62 0 0 1 1.567-.008c.336.178.579.276.819.308a2 2 0 0 0 1.479-.396c.315-.242.548-.646 1.014-1.453s.7-1.21.751-1.605a2 2 0 0 0-.396-1.479c-.148-.192-.355-.353-.676-.555A1.62 1.62 0 0 1 19.562 12c0-.558.304-1.064.777-1.36c.321-.203.529-.364.676-.556a2 2 0 0 0 .396-1.479c-.052-.394-.285-.798-.75-1.605c-.467-.807-.7-1.21-1.015-1.453a2 2 0 0 0-1.479-.396c-.24.032-.483.13-.82.308a1.62 1.62 0 0 1-1.566-.008a1.62 1.62 0 0 1-.79-1.353c-.014-.38-.05-.64-.143-.863a2 2 0 0 0-1.083-1.083Z" />
                </g>
              </svg>
              Pengaturan
            </a>
            <?php endif; ?>
          </div>
          <hr class="menu__separator" role="separator" />
          <a href="<?= base_url('logout') ?>" class="menu__item" role="menuitem">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
              <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5">
                <path d="M9.002 7c.012-2.175.109-3.353.877-4.121C10.758 2 12.172 2 15 2h1c2.829 0 4.243 0 5.122.879C22 3.757 22 5.172 22 8v8c0 2.828 0 4.243-.878 5.121C20.242 22 18.829 22 16 22h-1c-2.828 0-4.242 0-5.121-.879c-.768-.768-.865-1.946-.877-4.121" />
                <path stroke-linejoin="round" d="M15 12H2m0 0l3.5-3M2 12l3.5 3" />
              </g>
            </svg>
            Logout
          </a>
        </div>
      </div>
    </div>
  </div>
</header>
