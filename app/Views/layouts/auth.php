<!doctype html>
<html lang="id" data-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php
    $favicon = setting('App.siteFavicon');
    $faviconUrl = ! empty($favicon) ? base_url($favicon) : base_url('assets/img/stisla-fill.svg');
  ?>
  <link rel="icon" href="<?= esc($faviconUrl, 'attr') ?>" />
  <script>
    (function () {
      var t = localStorage.getItem("stisla-theme");
      if (t === "dark" || t === "light") document.documentElement.dataset.theme = t;
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" />
  <title><?= $title ?? 'Auth' ?> &mdash; <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?></title>

  <?php
    $authPitchTitle = trim($this->renderSection('auth_pitch_title'));
    $authPitchLede = trim($this->renderSection('auth_pitch_lede'));
    $authAsideStart = setting('App.authAsideStart') ?? '#2f3f63';
    $authAsideEnd = setting('App.authAsideEnd') ?? '#1b2338';

    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $authAsideStart)) {
      $authAsideStart = '#2f3f63';
    }

    if (! preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $authAsideEnd)) {
      $authAsideEnd = '#1b2338';
    }

    if ($authPitchTitle === '') {
      $authPitchTitle = 'Akses aman, <span>proses</span> <span>lebih tertata.</span>';
    }

    if ($authPitchLede === '') {
      $authPitchLede = 'Kelola akun dan aktivitas Anda melalui alur yang jelas, efisien, dan konsisten.';
    }
  ?>

  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>" />
  <?= $this->renderSection('css') ?>
</head>
<body>
  <main class="auth">
    <section class="auth__panel">
      <button
        type="button"
        class="button button--ghost button--neutral button--icon-only auth__toggle"
        data-theme-toggle
        aria-label="Toggle theme"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="currentColor" d="m21.067 11.857l-.642-.388zm-8.924-8.924l-.388-.642zM21.25 12A9.25 9.25 0 0 1 12 21.25v1.5c5.937 0 10.75-4.813 10.75-10.75zM12 21.25A9.25 9.25 0 0 1 2.75 12h-1.5c0 5.937 4.813 10.75 10.75 10.75zM2.75 12A9.25 9.25 0 0 1 12 2.75v-1.5C6.063 1.25 1.25 6.063 1.25 12zm12.75 2.25A5.75 5.75 0 0 1 9.75 8.5h-1.5a7.25 7.25 0 0 0 7.25 7.25zm4.925-2.781A5.75 5.75 0 0 1 15.5 14.25v1.5a7.25 7.25 0 0 0 6.21-3.505zM9.75 8.5a5.75 5.75 0 0 1 2.781-4.925l-.776-1.284A7.25 7.25 0 0 0 8.25 8.5zM12 2.75a.38.38 0 0 1-.268-.118a.3.3 0 0 1-.082-.155c-.004-.031-.002-.121.105-.186l.776 1.284c.503-.304.665-.861.606-1.299c-.062-.455-.42-1.026-1.137-1.026zm9.71 9.495c-.066.107-.156.109-.187.105a.3.3 0 0 1-.155-.082a.38.38 0 0 1-.118-.268h1.5c0-.717-.571-1.075-1.026-1.137c-.438-.059-.995.103-1.299.606z" />
        </svg>
      </button>

      <!-- Flash Messages -->
      <?php if (session()->getFlashdata('success')): ?>
      <!-- <div class="alert alert--success mb-3">
        <?= session()->getFlashdata('success') ?>
      </div> -->
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
      <!-- <div class="alert alert--danger mb-3">
        <?= session()->getFlashdata('error') ?>
      </div> -->
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </section>

    <aside class="auth__aside" style="background: linear-gradient(160deg, <?= esc($authAsideStart, 'attr') ?> 0%, <?= esc($authAsideEnd, 'attr') ?> 55%);">
      <a href="<?= base_url() ?>" class="auth__brand">
        <span class="auth__brand-mark">
          <?php
            $siteName = setting('App.siteName') ?? 'CI4 RBAC';
            $logo = setting('App.siteLogo');
            $logoUrl = ! empty($logo) ? base_url($logo) : base_url('assets/img/stisla-fill.svg');
          ?>
          <img src="<?= esc($logoUrl, 'attr') ?>" alt="<?= esc($siteName) ?>" style="height: 1em; width: auto;">
        </span>
        <span class="auth__brand-text">
          <span class="auth__brand-name"><?= esc($siteName) ?></span>
        </span>
      </a>
      <div class="auth__pitch">
        <h2 class="auth__pitch-title"><?= $authPitchTitle ?></h2>
        <p class="auth__pitch-lede"><?= $authPitchLede ?></p>
      </div>
    </aside>
  </main>

  <script type="module" src="https://cdn.jsdelivr.net/npm/@stisla/vanilla@3/dist/stisla.js"></script>
  <script src="<?= base_url('assets/js/theme.js') ?>"></script>
  <script>
    document.addEventListener("click", function (event) {
      var toggle = event.target.closest("[data-password-toggle]");
      if (!toggle) return;
      var input = document.getElementById(toggle.getAttribute("aria-controls"));
      if (!input) return;
      var reveal = input.type === "password";
      input.type = reveal ? "text" : "password";
      toggle.setAttribute("aria-pressed", String(reveal));
    });
  </script>
  <?= $this->renderSection('js') ?>
</body>
</html>
