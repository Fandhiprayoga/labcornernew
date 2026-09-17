<?= $this->section('css') ?>
<style>
  .notification-item {
    transition: background-color .2s ease, box-shadow .2s ease, transform .2s ease;
  }

  .notification-item:hover {
    background-color: var(--color-accent);
    transform: translateX(2px);
  }

  .badge svg {
    width: 0.9em;
    height: 0.9em;
    flex-shrink: 0;
  }
</style>
<?= $this->endSection() ?>

<div class="page__section">
  <div class="card">
    <div class="card__header flex items-center justify-between">
      <span class="card__title">Notifikasi Saya</span>
      <?php if ($unreadCount > 0): ?>
      <form action="<?= base_url('notifications/mark-all-read') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="button button--sm button--outline button--neutral">Tandai Semua Dibaca</button>
      </form>
      <?php endif; ?>
    </div>
    <div class="card__body p-0">
      <?php if (empty($notifications)): ?>
        <?= view('partials/empty_table_state', ['message' => 'Belum ada notifikasi.']) ?>
      <?php else: ?>
        <ul class="list-group list-group--flush">
          <?php foreach ($notifications as $notif): ?>
            <?php
              $badgeClass = match ($notif['type']) {
                'success' => 'success',
                'warning' => 'warning',
                'danger'  => 'danger',
                default   => 'info',
              };
              $typeIcon = match ($notif['type']) {
                'success' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="m5 12 4 4L19 6" />',
                'warning' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a1 1 0 0 0 .86 1.5h18.64a1 1 0 0 0 .86-1.5L13.71 3.86a1 1 0 0 0-1.72 0Z" />',
                'danger'  => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v4m0 4h.01M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" />',
                default   => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Zm0 8v5m0-8h.01" />',
              };
              $moduleIcon = match ($notif['module'] ?? null) {
                'asset_loan_proposal' => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 7.5 12 3l9 4.5M3 7.5v9L12 21m-9-4.5L12 21m9-4.5V7.5M12 21v-9M3 7.5 12 12l9-4.5" />',
                'loan_proposal'       => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 3h6a1 1 0 0 1 1 1v1h1a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1V4a1 1 0 0 1 1-1Zm0 6h6m-6 4h6m-6 4h4" />',
                default               => '<path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16" />',
              };
              $moduleLabel = ucwords(str_replace('_', ' ', (string) ($notif['module'] ?? '')));
            ?>
            <li class="list-group__item notification-item flex items-start justify-between gap-3 <?= ! $notif['is_read'] ? 'bg-muted' : '' ?>">
              <a href="<?= base_url('notifications/read/' . $notif['id']) ?>" class="flex-1 text-decoration-none">
                <div class="flex items-center gap-2 mb-1">
                  <span class="badge badge--soft badge--<?= $badgeClass ?>" title="<?= esc(ucfirst($notif['type'])) ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><?= $typeIcon ?></svg>
                    <!-- <?= esc(ucfirst($notif['type'])) ?> -->
                  </span>
                  <?php if (! empty($notif['module'])): ?>
                    <span class="badge badge--soft badge--secondary" title="<?= esc($moduleLabel) ?>">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><?= $moduleIcon ?></svg>
                      <?= esc($moduleLabel) ?>
                    </span>
                  <?php endif; ?>
                  <?php if (! $notif['is_read']): ?>
                    <span class="badge badge--danger">
                      <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3" /><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18Z" /></svg>
                      Baru
                    </span>
                  <?php endif; ?>
                </div>
                <div class="font-medium"><?= esc($notif['title']) ?></div>
                <?php if (! empty($notif['message'])): ?>
                  <div class="text-muted-foreground text-sm"><?= esc($notif['message']) ?></div>
                <?php endif; ?>
                <div class="text-muted-foreground text-xs mt-1"><?= esc($notif['created_at']) ?></div>
              </a>
              <form action="<?= base_url('notifications/delete/' . $notif['id']) ?>" method="post" onsubmit="return confirm('Hapus notifikasi ini?');">
                <?= csrf_field() ?>
                <button type="submit" class="button button--danger button--icon-only button--sm" title="Hapus" aria-label="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" />
                  </svg>
                </button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </div>
</div>
