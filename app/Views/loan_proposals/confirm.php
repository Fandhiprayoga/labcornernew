<?php
/** @var array $proposal */
/** @var array $cart */

$fmt = static fn (string $value): string => date('d M Y H:i', strtotime($value));
?>
<style>
  .loan-confirm__grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; }
  .loan-confirm__details { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1rem; }
  .loan-confirm__item { display: flex; gap: .75rem; align-items: flex-start; padding: .875rem 0; border-bottom: 1px solid var(--color-border); }
  .loan-confirm__item:last-child { border-bottom: 0; padding-bottom: 0; }
  .loan-confirm__item:first-child { padding-top: 0; }
  @media (min-width: 40rem) { .loan-confirm__details { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (min-width: 64rem) { .loan-confirm__grid { grid-template-columns: minmax(0, 1fr) 20rem; } }
</style>
<div class="page__section flex flex-col gap-4">
  <div class="card">
    <div class="card__header">
      <span class="card__title">Konfirmasi Proposal Peminjaman</span>
      <div class="card__action">
        <a href="<?= base_url('peminjaman/lab-loans/items/' . $proposal['uuid']) ?>" class="button button--outline button--neutral button--sm">Kembali</a>
      </div>
    </div>
    <div class="card__body">
      <p class="text-sm text-muted-foreground" style="margin:0 0 1rem;">Periksa kembali data proposal dan ruangan yang akan dipinjam sebelum mengajukan.</p>
      <div class="loan-confirm__details">
        <div><div class="text-xs text-muted-foreground">Nama Kegiatan</div><strong><?= esc($proposal['event_name']) ?></strong></div>
        <div><div class="text-xs text-muted-foreground">Tanggal Proposal</div><strong><?= esc(date('d M Y', strtotime($proposal['proposal_date']))) ?></strong></div>
        <div><div class="text-xs text-muted-foreground">Pemohon</div><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?></div></div>
        <div><div class="text-xs text-muted-foreground">Kontak</div><strong><?= esc($proposal['phone']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['email']) ?></div></div>
        <div><div class="text-xs text-muted-foreground">Waktu Mulai</div><strong><?= esc($fmt($proposal['event_start'])) ?></strong></div>
        <div><div class="text-xs text-muted-foreground">Waktu Selesai</div><strong><?= esc($fmt($proposal['event_end'])) ?></strong></div>
      </div>
    </div>
  </div>

  <div class="loan-confirm__grid">
    <div class="card">
      <div class="card__header">
        <span class="card__title">Ruangan yang Dipinjam</span>
        <span class="badge badge--soft badge--primary"><?= count($cart) ?> ruangan</span>
      </div>
      <div class="card__body">
        <?php foreach ($cart as $item): ?>
        <div class="loan-confirm__item">
          <img src="<?= base_url($item['laboratory_photo'] ?: 'assets/images/default-laboratory.svg') ?>" alt="" width="64" height="64" style="object-fit:cover;border-radius:.5rem;flex:0 0 64px;">
          <div style="min-width:0;">
            <strong><?= esc($item['laboratory_name']) ?></strong>
            <div class="text-sm text-muted-foreground"><?= esc($item['room_code'] . ' - ' . $item['room_name']) ?></div>
            <div class="text-xs text-muted-foreground">
              <?= esc(($item['building'] ?? '') ?: '-') ?><?php if ($item['floor'] !== null): ?> &middot; Lantai <?= (int) $item['floor'] ?><?php endif; ?>
              &middot; Kapasitas <?= (int) $item['capacity'] ?>
            </div>
            <?php if (! empty($item['notes'])): ?><div class="text-xs" style="margin-top:.25rem;">Catatan: <?= esc($item['notes']) ?></div><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="card__body">
        <div style="display:flex;justify-content:center;margin:0 0 1rem;">
          <svg viewBox="0 0 200 200" role="img" aria-label="Peringatan" xmlns="http://www.w3.org/2000/svg" width="200" height="200"><defs><linearGradient id="loan-confirm-warning-light" x1=".15" y1=".05" x2=".85" y2=".95"><stop offset="0" stop-color="oklch(0.8548 0.0627173 257.676)"/><stop offset="1" stop-color="oklch(0.75231 0.106953 257.676)"/></linearGradient><linearGradient id="loan-confirm-warning" x1=".15" y1=".05" x2=".85" y2=".95"><stop offset="0" stop-color="oklch(0.75231 0.106953 257.676)"/><stop offset="1" stop-color="oklch(0.649821 0.151189 257.676)"/></linearGradient><filter id="loan-confirm-warning-shadow" x="-40%" y="-40%" width="180%" height="180%"><feDropShadow dx="5" dy="9" stdDeviation="5.5" flood-color="oklch(0.572953 0.184366 257.676 / 0.26)"/></filter></defs><circle cx="100" cy="94" r="72" fill="oklch(0.572953 0.184366 257.676 / 0.06)"/><circle cx="100" cy="94" r="55" fill="oklch(0.572953 0.184366 257.676 / 0.1)"/><g filter="url(#loan-confirm-warning-shadow)"><path d="M100 54q5 0 8 5l44 76q4 7-4 7H52q-8 0-4-7l44-76q3-5 8-5z" fill="url(#loan-confirm-warning)"/><rect x="95" y="84" width="10" height="34" rx="5" fill="#fff"/><circle cx="100" cy="129" r="5.5" fill="#fff"/></g></svg>
        </div>
        <div class="text-sm text-muted-foreground" style="margin-bottom:1rem;">Dengan mengajukan proposal, data di atas akan dikirim untuk diproses.</div>
        <form action="<?= base_url('peminjaman/lab-loans/submit/' . $proposal['uuid']) ?>" method="post" onsubmit="return confirm('Ajukan proposal peminjaman ini?')">
          <?= csrf_field() ?>
          <button type="submit" class="button button--primary w-full">
            Ajukan Proposal
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 12h14m-6-6 6 6-6 6" /></svg>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
