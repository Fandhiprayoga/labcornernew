<?php
$currentUser = auth()->user();
$currentUrl  = uri_string();

function isMenuActive(string $path): string
{
  $currentUrl = uri_string();
  return (strpos($currentUrl, $path) !== false) ? 'active' : '';
}

function isDropdownActive(array $paths): string
{
  $currentUrl = uri_string();
  foreach ($paths as $path) {
    if (strpos($currentUrl, $path) !== false) {
      return 'active';
    }
  }
  return '';
}
?>
<aside class="sidebar sidebar--lg sidebar--app" data-stisla-sidebar>
  <header class="sidebar__header">
    <a class="sidebar__brand" href="<?= base_url('dashboard') ?>">
      <?php
      $siteName = setting('App.siteName') ?? 'CI4 RBAC';
      $logo = setting('App.siteLogo');
      $logoUrl = ! empty($logo) ? base_url($logo) : base_url('assets/img/stisla-fill.svg');
      ?>
      <img src="<?= esc($logoUrl, 'attr') ?>" alt="<?= esc($siteName) ?>" style="height: 1.5em; width: auto;">
      <span><?= esc($siteName) ?></span>
    </a>
  </header>

  <div class="sidebar__search">
    <div class="input-group input-group--search">
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
  </div>

  <div class="sidebar__content">
    <nav class="sidebar__menu">
      <!-- Dashboard -->
      <div class="sidebar__group">
        <span class="sidebar__group-title">Menu</span>
        <ul class="sidebar__list">
          <li class="sidebar__item <?= isMenuActive('dashboard') && !str_contains($currentUrl, 'admin') ? 'active' : '' ?>">
            <a class="sidebar__button" href="<?= base_url('dashboard') ?>" <?= isMenuActive('dashboard') && !str_contains($currentUrl, 'admin') ? 'aria-current="page"' : '' ?>>
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M2 6.5c0-2.121 0-3.182.659-3.841S4.379 2 6.5 2s3.182 0 3.841.659S11 4.379 11 6.5s0 3.182-.659 3.841S8.621 11 6.5 11s-3.182 0-3.841-.659S2 8.621 2 6.5m11 11c0-2.121 0-3.182.659-3.841S15.379 13 17.5 13s3.182 0 3.841.659S22 15.379 22 17.5s0 3.182-.659 3.841S19.621 22 17.5 22s-3.182 0-3.841-.659S13 19.621 13 17.5" opacity=".5" />
                <path fill="currentColor" d="M2 17.5c0-2.121 0-3.182.659-3.841S4.379 13 6.5 13s3.182 0 3.841.659S11 15.379 11 17.5s0 3.182-.659 3.841S8.621 22 6.5 22s-3.182 0-3.841-.659S2 19.621 2 17.5m11-11c0-2.121 0-3.182.659-3.841S15.379 2 17.5 2s3.182 0 3.841.659S22 4.379 22 6.5s0 3.182-.659 3.841S19.621 11 17.5 11s-3.182 0-3.841-.659S13 8.621 13 6.5" />
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- Admin Menu -->
      <?php if (activeGroupCan('admin.access')): ?>
        <div class="sidebar__group">
          <span class="sidebar__group-title">Data Master</span>
          <ul class="sidebar__list">
            <!-- Room Management -->
            <?php if (activeGroupCan('rooms.list')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/rooms') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/rooms') ?>" <?= isMenuActive('admin/rooms') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M4 21V3h16v18h-2V5H6v16z" opacity=".5" />
                    <path fill="currentColor" d="M8 8h3v3H8zm5 0h3v3h-3zM8 13h3v3H8zm5 0h3v3h-3z" />
                  </svg>
                  <span>Master Ruangan</span>
                </a>
              </li>
            <?php endif; ?>

            <!-- Faculty Management -->
            <?php if (activeGroupCan('faculties.list')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/faculties') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/faculties') ?>" <?= isMenuActive('admin/faculties') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M3 10.5 12 4l9 6.5v2H3zM5 14h2v5H5zm4 0h2v5H9zm4 0h2v5h-2zm4 0h2v5h-2zM3 21h18v-1.5H3z" />
                  </svg>
                  <span>Master Fakultas</span>
                </a>
              </li>
            <?php endif; ?>

            <!-- Study Program Management -->
            <?php if (activeGroupCan('study-programs.list')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/study-programs') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/study-programs') ?>" <?= isMenuActive('admin/study-programs') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M4 4h16v16H4z" opacity=".35" />
                    <path fill="currentColor" d="M7 7h2v2H7zm4 0h6v2h-6zM7 11h2v2H7zm4 0h6v2h-6zM7 15h2v2H7zm4 0h6v2h-6z" />
                  </svg>
                  <span>Program Studi</span>
                </a>
              </li>
            <?php endif; ?>

            <!-- Laboratory Management -->
            <?php if (activeGroupCan('laboratories.list')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/laboratories') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/laboratories') ?>" <?= isMenuActive('admin/laboratories') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M4 4h16v16H4z" opacity=".35" />
                    <path fill="currentColor" d="M8 7h8v2H8zm2 4h4v2h-4zm-2 4h8v2H8z" />
                  </svg>
                  <span>Master Laboratorium</span>
                </a>
              </li>
            <?php endif; ?>

            <!-- Laboratory Laboran Assignment -->
            <?php if (activeGroupCan('laboratory-laborans.list')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/laboratory-laborans') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/laboratory-laborans') ?>" <?= isMenuActive('admin/laboratory-laborans') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="8" cy="7" r="3" fill="currentColor" opacity=".45" />
                    <path fill="currentColor" d="M3 18c0-2.5 2.2-4.5 5-4.5s5 2 5 4.5V20H3zM14 5h7v2h-7zm0 5h7v2h-7zm2 5h5v2h-5z" />
                  </svg>
                  <span>Master Laboran</span>
                </a>
              </li>
            <?php endif; ?>

            <!-- Asset Management -->
            <?php if (activeGroupCan('assets.list')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/assets') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/assets') ?>" <?= isMenuActive('admin/assets') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M4 7h16v12H4z" opacity=".35" />
                    <path fill="currentColor" d="M7 5h10v3H7zm1 6h8v2H8zm0 4h5v2H8z" />
                  </svg>
                  <span>Master Asset</span>
                </a>
              </li>
            <?php endif; ?>

          </ul>
        </div>

        <div class="sidebar__group">
          <span class="sidebar__group-title">Administrasi</span>
          <ul class="sidebar__list">
            <!-- User Management -->
            <?php if (activeGroupCan('users.list')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/users') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/users') ?>" <?= isMenuActive('admin/users') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="15" cy="6" r="3" fill="currentColor" opacity=".4" />
                    <ellipse cx="16" cy="17" fill="currentColor" opacity=".4" rx="5" ry="3" />
                    <circle cx="9.001" cy="6" r="4" fill="currentColor" />
                    <ellipse cx="9.001" cy="17.001" fill="currentColor" rx="7" ry="4" />
                  </svg>
                  <span>Manajemen User</span>
                </a>
              </li>
            <?php endif; ?>

            <!-- Role Management -->
            <?php if (activeGroupIs('superadmin')): ?>
              <li class="sidebar__item" data-state="<?= isDropdownActive(['admin/roles']) ? 'open' : 'closed' ?>">
                <a class="sidebar__button" href="<?= base_url('admin/roles') ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" d="M2 16c0-2.828 0-4.243.879-5.121C3.757 10 5.172 10 8 10h8c2.828 0 4.243 0 5.121.879C22 11.757 22 13.172 22 16s0 4.243-.879 5.121C20.243 22 18.828 22 16 22H8c-2.828 0-4.243 0-5.121-.879C2 20.243 2 18.828 2 16" opacity=".5" />
                    <path fill="currentColor" d="M12 18a2 2 0 1 0 0-4a2 2 0 0 0 0 4M6.75 8a5.25 5.25 0 0 1 10.5 0v2.004c.567.005 1.064.018 1.5.05V8a6.75 6.75 0 0 0-13.5 0v2.055a24 24 0 0 1 1.5-.051z" />
                  </svg>
                  <span>Role & Permission</span>
                </a>
                <button type="button" class="sidebar__item-action" data-stisla-sidebar-submenu-toggle
                  aria-expanded="<?= isDropdownActive(['admin/roles']) ? 'true' : 'false' ?>"
                  aria-controls="nav-roles" aria-label="Toggle Role & Permission submenu">
                  <span class="sidebar__caret"></span>
                </button>
                <div class="sidebar__submenu" id="nav-roles">
                  <ul class="sidebar__list">
                    <li class="sidebar__item <?= isMenuActive('admin/roles') && !str_contains($currentUrl, 'permissions') ? 'active' : '' ?>">
                      <a class="sidebar__button" href="<?= base_url('admin/roles') ?>"><span>Daftar Role</span></a>
                    </li>
                    <li class="sidebar__item <?= isMenuActive('admin/roles/permissions') ? 'active' : '' ?>">
                      <a class="sidebar__button" href="<?= base_url('admin/roles/permissions') ?>"><span>Permission Matrix</span></a>
                    </li>
                  </ul>
                </div>
              </li>
            <?php endif; ?>

            <!-- Settings -->
            <?php if (activeGroupCan('admin.settings')): ?>
              <li class="sidebar__item <?= isMenuActive('admin/settings') ?>">
                <a class="sidebar__button" href="<?= base_url('admin/settings') ?>" <?= isMenuActive('admin/settings') ? 'aria-current="page"' : '' ?>>
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="currentColor" fill-rule="evenodd" d="M14.279 2.152C13.909 2 13.439 2 12.5 2s-1.408 0-1.779.152a2 2 0 0 0-1.09 1.083c-.094.223-.13.484-.145.863a1.62 1.62 0 0 1-.796 1.353a1.64 1.64 0 0 1-1.579.008c-.338-.178-.583-.276-.825-.308a2.03 2.03 0 0 0-1.49.396c-.318.242-.553.646-1.022 1.453c-.47.807-.704 1.21-.757 1.605c-.07.526.074 1.058.4 1.479c.148.192.357.353.68.555c.477.297.783.803.783 1.361s-.306 1.064-.782 1.36c-.324.203-.533.364-.682.556a2 2 0 0 0-.399 1.479c.053.394.287.798.757 1.605s.704 1.21 1.022 1.453c.424.323.96.465 1.49.396c.242-.032.487-.13.825-.308a1.64 1.64 0 0 1 1.58.008c.486.28.774.795.795 1.353c.015.38.051.64.145.863c.204.49.596.88 1.09 1.083c.37.152.84.152 1.779.152s1.409 0 1.779-.152a2 2 0 0 0 1.09-1.083c.094-.223.13-.483.145-.863c.02-.558.309-1.074.796-1.353a1.64 1.64 0 0 1 1.579-.008c.338.178.583.276.825.308c.53.07 1.066-.073 1.49-.396c.318-.242.553-.646 1.022-1.453c.47-.807.704-1.21.757-1.605a2 2 0 0 0-.4-1.479c-.148-.192-.357-.353-.68-.555c-.477-.297-.783-.803-.783-1.361s.306-1.064.782-1.36c.324-.203.533-.364.682-.556a2 2 0 0 0 .399-1.479c-.053-.394-.287-.798-.757-1.605s-.704-1.21-1.022-1.453a2.03 2.03 0 0 0-1.49-.396c-.242.032-.487.13-.825.308a1.64 1.64 0 0 1-1.58-.008a1.62 1.62 0 0 1-.795-1.353c-.015-.38-.051-.64-.145-.863a2 2 0 0 0-1.09-1.083" clip-rule="evenodd" opacity=".5" />
                    <path fill="currentColor" d="M15.523 12c0 1.657-1.354 3-3.023 3s-3.023-1.343-3.023-3S10.83 9 12.5 9s3.023 1.343 3.023 3" />
                  </svg>
                  <span>Pengaturan</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>
      <?php endif; ?>

      <!-- Akun -->
      <div class="sidebar__group">
        <span class="sidebar__group-title">Akun</span>
        <ul class="sidebar__list">
          <li class="sidebar__item <?= isMenuActive('profile') ?>">
            <a class="sidebar__button" href="<?= base_url('profile') ?>" <?= isMenuActive('profile') ? 'aria-current="page"' : '' ?>>
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12S6.477 2 12 2s10 4.477 10 10" opacity=".5" />
                <path fill="currentColor" d="M16.807 19.011A8.46 8.46 0 0 1 12 20.5a8.46 8.46 0 0 1-4.807-1.489c-.604-.415-.862-1.205-.51-1.848C7.41 15.83 8.91 15 12 15s4.59.83 5.318 2.163c.35.643.093 1.433-.511 1.848M12 12a3 3 0 1 0 0-6a3 3 0 0 0 0 6" />
              </svg>
              <span>Profil Saya</span>
            </a>
          </li>
          <li class="sidebar__item <?= isMenuActive('notifications') ?>">
            <a class="sidebar__button" href="<?= base_url('notifications') ?>" <?= isMenuActive('notifications') ? 'aria-current="page"' : '' ?>>
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                <path fill="currentColor" d="M3.833 8.406c.208-3.696 3.164-6.652 6.86-6.86a26 26 0 0 1 2.614 0c3.696.208 6.652 3.164 6.86 6.86c.045.806.045 1.622 0 2.428a10 10 0 0 0 1.535 5.474l.238.376c.945 1.49.223 3.482-1.462 3.996a30.7 30.7 0 0 1-17.956 0c-1.685-.514-2.407-2.506-1.462-3.996l.238-.376a10 10 0 0 0 1.535-5.474a25 25 0 0 1 0-2.428" opacity=".5" />
                <path fill="currentColor" d="M7.828 20.24a4.185 4.185 0 0 0 8.344 0c-2.767.32-5.577.32-8.344 0" />
              </svg>
              <span>Notifikasi</span>
              <?php $unread = notification()->unreadCount($currentUser->id); ?>
              <?php if ($unread > 0): ?>
                <span class="badge badge--danger ms-auto"><?= $unread ?></span>
              <?php endif; ?>
            </a>
          </li>
        </ul>
      </div>
    </nav>
  </div>

  <footer class="sidebar__footer">
    <ul class="sidebar__list">
      <li class="sidebar__item">
        <a class="sidebar__button" href="<?= base_url('logout') ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="currentColor" d="M16 2h-1c-2.829 0-4.242 0-5.121.879S9 5.172 9 8v8c0 2.829 0 4.243.879 5.122c.878.878 2.292.878 5.119.878H16c2.828 0 4.242 0 5.121-.879C22 20.243 22 18.828 22 16V8c0-2.828 0-4.243-.879-5.121S18.828 2 16 2" opacity=".5" />
            <path fill="currentColor" fill-rule="evenodd" d="M15.75 12a.75.75 0 0 0-.75-.75H4.027l1.961-1.68a.75.75 0 1 0-.976-1.14l-3.5 3a.75.75 0 0 0 0 1.14l3.5 3a.75.75 0 1 0 .976-1.14l-1.96-1.68H15a.75.75 0 0 0 .75-.75" clip-rule="evenodd" />
          </svg>
          <span>Logout</span>
        </a>
      </li>
    </ul>
    <div class="copyright">
      <hr class="separator my-3" style="--separator-color: var(--sidebar-submenu-border-color)" />
      <p class="text-xs text-muted-foreground" style="--link-color: var(--color-foreground)">
        &copy; <?= date('Y') ?> <?= esc(setting('App.siteFooter') ?? 'CI4 Shield RBAC') ?>
      </p>
    </div>
  </footer>
</aside>