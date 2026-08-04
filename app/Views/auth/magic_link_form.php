<?= $this->extend('layouts/auth') ?>

<?= $this->section('auth_pitch_title') ?>
Pulihkan akses <span>secara aman</span> <span>dan efisien.</span>
<?= $this->endSection() ?>

<?= $this->section('auth_pitch_lede') ?>
Sistem akan mengirim tautan masuk ke email Anda agar proses autentikasi tetap praktis.
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="auth__form">
  <div>
    <h1 class="text-2xl">Tautan Masuk</h1>
    <p class="text-muted-foreground mt-1">Silakan masukkan alamat email untuk menerima tautan masuk.</p>
  </div>

  <?php if (session('error') !== null) : ?>
    <div class="alert alert--danger">
      <div class="alert__body"><?= esc(session('error')) ?></div>
    </div>
  <?php endif ?>

  <?php if (session('errors') !== null) : ?>
    <div class="alert alert--danger">
      <div class="alert__body">
        <?php if (is_array(session('errors'))) : ?>
          <?php foreach (session('errors') as $error) : ?>
            <p class="mb-0"><?= esc($error) ?></p>
          <?php endforeach ?>
        <?php else : ?>
          <p class="mb-0"><?= esc(session('errors')) ?></p>
        <?php endif ?>
      </div>
    </div>
  <?php endif ?>

  <?php if (session('message') !== null) : ?>
    <div class="alert alert--success">
      <div class="alert__body"><?= session('message') ?></div>
    </div>
  <?php endif ?>

  <form method="POST" action="<?= url_to('magic-link') ?>" class="flex flex-col gap-4">
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
        <input type="email" class="input" id="email" name="email" value="<?= old('email', auth()->user()->email ?? null) ?>" placeholder="email@example.com" autocomplete="email" tabindex="1" required autofocus>
      </div>
    </div>

    <button type="submit" class="button button--primary button--block button--lg" tabindex="2">
      <?= lang('Auth.send') ?>
      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
        <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 12h16m0 0l-6-6m6 6l-6 6" />
      </svg>
    </button>
  </form>

  <p class="text-center text-sm text-muted-foreground">
    <a href="<?= url_to('login') ?>" class="link">← <?= lang('Auth.backToLogin') ?></a>
  </p>
</div>
<?= $this->endSection() ?>
