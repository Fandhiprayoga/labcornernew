<?= $this->extend('layouts/auth') ?>

<?= $this->section('auth_pitch_title') ?>
Layanan sedang <span>dalam pemeliharaan</span> <span>terjadwal.</span>
<?= $this->endSection() ?>

<?= $this->section('auth_pitch_lede') ?>
Kami sedang meningkatkan kualitas sistem agar layanan kembali tersedia dengan performa yang lebih optimal.
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth__form auth__maintenance">
  <div class="auth__maintenance-icon" aria-hidden="true">
    <svg xmlns="http://www.w3.org/2000/svg" width="4rem" height="4rem" viewBox="0 0 24 24">
      <g fill="none" stroke="currentColor" stroke-width="1.5">
        <path d="M3.464 20.536C4.93 22 7.286 22 12 22s7.071 0 8.535-1.465C22 19.072 22 16.714 22 12s0-7.071-1.465-8.536C19.072 2 16.714 2 12 2S4.929 2 3.464 3.464C2 4.93 2 7.286 2 12s0 7.071 1.464 8.536" />
        <path stroke-linecap="round" d="M14 2.05c2.042.184 3.303.72 4.21 1.627C19.518 4.984 20 7.264 20 12m-16 0c0-4.736.482-7.016 1.79-8.323A5.4 5.4 0 0 1 10 2.05" />
        <path stroke-linecap="round" d="M9.5 14.5s.5.5 2.5.5s2.5-.5 2.5-.5M8 18s1 1.5 4 1.5s4-1.5 4-1.5" />
      </g>
    </svg>
  </div>

  <div class="auth__maintenance-copy">
    <h2 class="text-lg font-semibold">Layanan Sedang Dalam Pemeliharaan</h2>
    <p class="text-muted-foreground">
      <?= esc(setting('App.maintenanceMsg') ?? 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.') ?>
    </p>
    <p class="text-muted-foreground text-xs">
      Kami akan segera kembali. Terima kasih atas kesabaran Anda.
    </p>
  </div>

  <?php if (auth()->loggedIn()): ?>
    <a href="<?= base_url('logout') ?>" class="button button--outline button--danger w-full">
      Keluar
    </a>
  <?php else: ?>
    <a href="<?= base_url('login') ?>" class="button button--primary w-full">
      Masuk sebagai Administrator
    </a>
  <?php endif; ?>

  <p class="auth__maintenance-meta text-muted-foreground text-xs">
    <?= esc(setting('App.siteName') ?? 'CI4 Shield RBAC') ?> - v<?= esc(setting('App.siteVersion') ?? '1.0.0') ?>
  </p>
</div>
<?= $this->endSection() ?>
