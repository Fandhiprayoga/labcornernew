<?= $this->extend('layouts/auth') ?>

<?= $this->section('auth_pitch_title') ?>
Masuk dan <span>lanjutkan aktivitas</span> <span>secara terarah.</span>
<?= $this->endSection() ?>

<?= $this->section('auth_pitch_lede') ?>
Akses dashboard Anda melalui langkah yang ringkas dengan pengalaman yang konsisten.
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth__form">
  <div>
    <h1 class="text-2xl">Selamat Datang</h1>
    <p class="text-muted-foreground mt-1">Silakan masuk ke dashboard Anda.</p>
  </div>

  <?php if (session('error') !== null) : ?>
    <div class="alert alert--danger">
      <div class="alert__body"><?= session('error') ?></div>
    </div>
  <?php endif ?>

  <?php if (session('errors') !== null) : ?>
    <div class="alert alert--danger">
      <div class="alert__body">
        <?php foreach (session('errors') as $error) : ?>
          <p class="mb-0"><?= $error ?></p>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

  <?php if (session('message') !== null) : ?>
    <div class="alert alert--success">
      <div class="alert__body"><?= session('message') ?></div>
    </div>
  <?php endif ?>

  <form method="POST" action="<?= url_to('login') ?>" class="flex flex-col gap-4">
    <?= csrf_field() ?>

    <div class="field">
      <label for="email" class="field__label">Email</label>
      <div class="input-group input-group--lg">
        <span class="input-group__text">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <g fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M2 12c0-3.771 0-5.657 1.172-6.828S6.229 4 10 4h4c3.771 0 5.657 0 6.828 1.172S22 8.229 22 12s0 5.657-1.172 6.828S17.771 20 14 20h-4c-3.771 0-5.657 0-6.828-1.172S2 15.771 2 12Z" />
              <path stroke-linecap="round" d="m6 8l2.159 1.8c1.837 1.53 2.755 2.295 3.841 2.295s2.005-.765 3.841-2.296L18 8" />
            </g>
          </svg>
        </span>
        <input type="email" class="input" id="email" name="email" value="<?= old('email') ?>" placeholder="email@example.com" autocomplete="email" required autofocus>
      </div>
    </div>

    <div class="field">
      <div class="flex items-center justify-between gap-2">
        <label for="password" class="field__label">Password</label>
        <a href="<?= url_to('magic-link') ?>" class="link text-xs">Lupa Kata Sandi?</a>
      </div>
      <div class="input-group input-group--lg">
        <span class="input-group__text">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <g fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M2 16c0-2.828 0-4.243.879-5.121C3.757 10 5.172 10 8 10h8c2.828 0 4.243 0 5.121.879C22 11.757 22 13.172 22 16s0 4.243-.879 5.121C20.243 22 18.828 22 16 22H8c-2.828 0-4.243 0-5.121-.879C2 20.243 2 18.828 2 16Z" />
              <circle cx="12" cy="16" r="2" />
              <path stroke-linecap="round" d="M6 10V8a6 6 0 1 1 12 0v2" />
            </g>
          </svg>
        </span>
        <input type="password" class="input" id="password" name="password" placeholder="••••••••••" autocomplete="current-password" required>
        <button type="button" class="input-group__text" data-password-toggle aria-controls="password" aria-label="Show password">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
            <g fill="none" stroke="currentColor" stroke-width="1.5">
              <path d="M3.275 15.296C2.425 14.192 2 13.639 2 12c0-1.64.425-2.191 1.275-3.296C4.972 6.5 7.818 4 12 4s7.028 2.5 8.725 4.704C21.575 9.81 22 10.361 22 12c0 1.64-.425 2.191-1.275 3.296C19.028 17.5 16.182 20 12 20s-7.028-2.5-8.725-4.704Z" />
              <path d="M15 12a3 3 0 1 1-6 0a3 3 0 0 1 6 0Z" />
            </g>
          </svg>
        </button>
      </div>
    </div>

    <div class="field__item">
      <input class="checkbox" type="checkbox" name="remember" id="remember-me" <?php if (old('remember')): ?>checked<?php endif ?>>
      <label class="field__label" for="remember-me">Tetap Masuk</label>
    </div>

    <button type="submit" class="button button--primary button--block button--lg">
      Masuk
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12h16m0 0l-6-6m6 6l-6 6" />
      </svg>
    </button>
  </form>

  <?php if (setting('Auth.allowRegistration')): ?>
  <p class="text-center text-sm text-muted-foreground">
    Belum memiliki akun? <a href="<?= url_to('register') ?>" class="link">Daftar</a>
  </p>
  <?php endif ?>
</div>
<?= $this->endSection() ?>
