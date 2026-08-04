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
  <title><?= $title ?? 'Dashboard' ?> &mdash; <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?></title>

  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>" />
  <?= $this->renderSection('css') ?>
</head>
<body>
  <div class="app-shell" data-stisla-app-shell data-stisla-app-shell-auto-collapse="true">
    <!-- Sidebar -->
    <?= $this->include('partials/sidebar') ?>

    <main class="app-shell__main">
      <!-- Navbar -->
      <?= $this->include('partials/navbar') ?>

      <div class="page content">
        <div class="content__container">
          <header class="page__header">
            <h1 class="page__title"><?= $page_title ?? 'Page' ?></h1>
            <?php if (isset($page_title) && $page_title !== 'Dashboard'): ?>
            <nav class="breadcrumb" aria-label="Breadcrumb">
              <ol class="breadcrumb__list">
                <li class="breadcrumb__item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
                <li class="breadcrumb__item"><span><?= $page_title ?></span></li>
              </ol>
            </nav>
            <?php endif; ?>
          </header>

          <div class="page__body">
            <!-- Flash Messages -->
            <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert--success alert--dismissible mb-4">
              <div class="alert__body">
                <?= session()->getFlashdata('success') ?>
              </div>
              <button type="button" class="alert__close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert--danger alert--dismissible mb-4">
              <div class="alert__body">
                <?= session()->getFlashdata('error') ?>
              </div>
              <button type="button" class="alert__close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert--danger alert--dismissible mb-4">
              <div class="alert__body">
                <ul class="mb-0" style="padding-left: 1rem;">
                  <?php foreach (session()->getFlashdata('errors') as $err): ?>
                  <li><?= esc($err) ?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <button type="button" class="alert__close" data-dismiss="alert">&times;</button>
            </div>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script type="module" src="https://cdn.jsdelivr.net/npm/@stisla/vanilla@3/dist/stisla.js"></script>
  <script src="<?= base_url('assets/js/theme.js') ?>"></script>
  <script src="<?= base_url('assets/js/app-shell.js') ?>"></script>
  <script>
    // Alert dismiss
    document.addEventListener("click", function (event) {
      var close = event.target.closest("[data-dismiss='alert']");
      if (!close) return;
      var alert = close.closest(".alert");
      if (alert) alert.remove();
    });
    // Password toggle
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

  <!-- Page Specific JS File -->
  <?= $this->renderSection('page_js') ?>
</body>
</html>
