<?php
/** @var array $proposal */
/** @var array $laboratories */
/** @var array $cart */
/** @var string $search */
/** @var bool $editable */
/** @var CodeIgniter\Pager\Pager $pager */
/** @var int $perPage */
/** @var int[] $perPageOptions */
/** @var int $totalRows */
/** @var int $currentPage */

$cartLabIds = array_map(static fn (array $item): int => (int) $item['laboratory_id'], $cart);
$hasSelectedLaboratory = ! empty($cart);
$statusLabels = ['draft' => 'Draft', 'submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'completed' => 'Selesai'];
$statusColors = ['draft' => 'secondary', 'submitted' => 'warning', 'laboran_approved' => 'info', 'rejected' => 'danger', 'approved' => 'success', 'completed' => 'primary'];
$fmt = static fn (string $value): string => date('d M Y H:i', strtotime($value));
?>
<style>
  .loan-items__layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1rem; align-items: start; }
  .loan-items__grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1rem; }
  .loan-items__card { border: 1px solid var(--color-border); border-radius: .75rem; overflow: hidden; display: flex; flex-direction: column; }
  .loan-items__media { position: relative; }
  .loan-items__zoom { position: absolute; top: .5rem; right: .5rem; display: inline-flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border: 0; border-radius: .5rem; cursor: pointer; color: #fff; background: rgb(0 0 0 / .55); backdrop-filter: blur(2px); }
  .loan-items__zoom:hover { background: rgb(0 0 0 / .75); }
  .loan-items__lightbox { position: fixed; inset: 0; z-index: 60; display: none; align-items: center; justify-content: center; padding: 1.5rem; background: rgb(0 0 0 / .75); }
  .loan-items__lightbox[open] { display: flex; }
  .loan-items__lightbox-panel { background: var(--color-card, #fff); border-radius: .75rem; overflow: hidden; max-width: 56rem; width: 100%; }
  .loan-items__lightbox-panel img { display: block; width: 100%; max-height: 70vh; object-fit: contain; background: #000; }
  .loan-items__summary { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1rem; }
  @media (min-width: 40rem) { .loan-items__grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .loan-items__summary { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
  @media (min-width: 64rem) { .loan-items__layout { grid-template-columns: minmax(0, 1fr) 20rem; } }
  @media (min-width: 90rem) { .loan-items__grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
</style>
<div class="page__section flex flex-col gap-4">

  <div class="card">
    <div class="card__header">
      <span class="card__title"><?= esc($proposal['event_name']) ?></span>
      <div class="card__action">
        <span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? ucfirst($proposal['status'])) ?></span>
        <a href="<?= base_url('peminjaman/lab-loans') ?>" class="button button--outline button--neutral button--sm">Kembali</a>
      </div>
    </div>
    <div class="card__body">
      <div class="loan-items__summary">
        <div><div class="text-xs text-muted-foreground">Pemohon</div><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?></div></div>
        <div><div class="text-xs text-muted-foreground">Kontak</div><strong><?= esc($proposal['phone']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['email']) ?></div></div>
        <div><div class="text-xs text-muted-foreground">Mulai</div><strong><?= esc($fmt($proposal['event_start'])) ?></strong></div>
        <div><div class="text-xs text-muted-foreground">Selesai</div><strong><?= esc($fmt($proposal['event_end'])) ?></strong></div>
      </div>
    </div>
  </div>

  <div class="loan-items__layout">

    <div>
      <div class="card">
        <div class="card__header"><span class="card__title">Katalog Ruangan Laboratorium</span></div>
        <div class="card__body" style="border-bottom:1px solid var(--color-border);">
          <form method="get" action="<?= base_url('peminjaman/lab-loans/items/' . $proposal['uuid']) ?>" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:.75rem;">
            <div style="flex:1 1 240px;min-width:200px;">
              <label class="text-xs text-muted-foreground" for="q">Cari laboratorium</label>
              <input type="search" class="input" id="q" name="q" value="<?= esc($search) ?>" placeholder="Nama lab, kode ruangan, atau gedung...">
            </div>
            <div style="flex:0 0 110px;">
              <label class="text-xs text-muted-foreground" for="perPage">Per halaman</label>
              <select class="select" id="perPage" name="perPage">
                <?php foreach ($perPageOptions as $option): ?><option value="<?= $option ?>" <?= $perPage === $option ? 'selected' : '' ?>><?= $option ?></option><?php endforeach; ?>
              </select>
            </div>
            <div style="display:flex;gap:.5rem;">
              <button type="submit" class="button button--primary button--sm">Filter</button>
              <?php if ($search !== ''): ?>
              <a href="<?= base_url('peminjaman/lab-loans/items/' . $proposal['uuid']) ?>" class="button button--outline button--sm">Reset</a>
              <?php endif; ?>
            </div>
          </form>
          <p class="text-xs text-muted-foreground" style="margin:.75rem 0 0;">Katalog hanya menampilkan laboratorium yang tersedia pada rentang kegiatan <?= esc($fmt($proposal['event_start'])) ?> &ndash; <?= esc($fmt($proposal['event_end'])) ?>. Satu proposal hanya dapat memilih satu laboratorium.</p>
        </div>
        <div class="card__body">
          <?php if (empty($laboratories)): ?>
            <?= view('partials/empty_table_state', ['message' => $search !== '' ? 'Tidak ada laboratorium yang cocok dengan pencarian tersebut.' : 'Tidak ada laboratorium yang tersedia pada rentang kegiatan ini.']) ?>
          <?php else: ?>
          <div class="loan-items__grid">
            <?php foreach ($laboratories as $laboratory): ?>
            <?php
              $labId = (int) $laboratory['id'];
              $inCart = in_array($labId, $cartLabIds, true);
              $photoUrl = base_url($laboratory['photo'] ?: 'assets/images/default-laboratory.svg');
              $photoCaption = $laboratory['name'] . ' - ' . $laboratory['room_code'] . ' ' . $laboratory['room_name'];
            ?>
            <div class="loan-items__card">
              <div class="loan-items__media">
                <img src="<?= esc($photoUrl, 'attr') ?>" alt="Foto <?= esc($laboratory['name']) ?>" style="width:100%;height:130px;object-fit:cover;">
                <button type="button" class="loan-items__zoom" title="Lihat detail foto ruangan" aria-label="Lihat detail foto ruangan" data-photo="<?= esc($photoUrl, 'attr') ?>" data-caption="<?= esc($photoCaption, 'attr') ?>">
                  <svg xmlns="http://www.w3.org/2000/svg" width="1.1em" height="1.1em" viewBox="0 0 24 24" aria-hidden="true"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.75"><circle cx="11" cy="11" r="6.5" /><path d="m20 20-4.2-4.2M8.5 11h5M11 8.5v5" /></g></svg>
                </button>
              </div>
              <div style="padding:.875rem;display:flex;flex-direction:column;gap:.5rem;flex:1;">
                <div class="flex items-start justify-between gap-2">
                  <div>
                    <strong><?= esc($laboratory['name']) ?></strong>
                    <div class="text-xs text-muted-foreground"><?= esc($laboratory['room_code'] . ' - ' . $laboratory['room_name']) ?></div>
                  </div>
                  <span class="badge badge--soft badge--success">Tersedia</span>
                </div>
                <div class="text-xs text-muted-foreground">
                  <?= esc(($laboratory['building'] ?? '') ?: '-') ?><?php if ($laboratory['floor'] !== null): ?> &middot; Lantai <?= (int) $laboratory['floor'] ?><?php endif; ?>
                  &middot; Kapasitas <?= (int) $laboratory['capacity'] ?>
                </div>

                <div style="margin-top:auto;">
                  <?php if ($inCart): ?>
                    <button type="button" class="button button--outline button--sm w-full" disabled>Sudah di cart</button>
                  <?php elseif ($hasSelectedLaboratory): ?>
                    <button type="button" class="button button--outline button--sm w-full" disabled>Satu lab sudah dipilih</button>
                  <?php elseif (! $editable): ?>
                    <button type="button" class="button button--outline button--sm w-full" disabled>Tidak dapat diubah</button>
                  <?php else: ?>
                  <form action="<?= base_url('peminjaman/lab-loans/items/' . $proposal['uuid'] . '/add') ?>" method="post" class="flex flex-col gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="laboratory_id" value="<?= $labId ?>">
                    <button type="submit" class="button button--primary button--sm w-full">Tambah ke Cart</button>
                  </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php if ($totalRows > 0): ?>
        <div class="card__body" style="border-top:1px solid var(--color-border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem;">
          <span class="text-xs text-muted-foreground">
            Menampilkan <?= $laboratories ? (($currentPage - 1) * $perPage) + 1 : 0 ?>&ndash;<?= (($currentPage - 1) * $perPage) + count($laboratories) ?> dari <?= $totalRows ?> laboratorium
          </span>
          <?= $pager->only(['q', 'perPage'])->links('default', 'app') ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <div>
      <div class="card">
        <div class="card__header">
          <span class="card__title">Cart Peminjaman</span>
          <div class="card__action"><span class="badge badge--soft badge--primary"><?= count($cart) ?> ruangan</span></div>
        </div>
        <div class="card__body">
          <?php if (empty($cart)): ?>
            <div class="flex flex-col items-center gap-2 py-4 text-center">
              <img src="<?= base_url('assets/img/empty-cart.svg') ?>" alt="" width="120" height="120">
              <p class="text-sm text-muted-foreground" style="margin:0;">Belum ada ruangan laboratorium yang dipilih.</p>
            </div>
          <?php else: ?>
          <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:.75rem;">
            <?php foreach ($cart as $item): ?>
            <li style="border:1px solid var(--color-border);border-radius:.625rem;padding:.75rem;display:flex;gap:.75rem;align-items:flex-start;">
              <img src="<?= base_url($item['laboratory_photo'] ?: 'assets/images/default-laboratory.svg') ?>" alt="" width="44" height="44" style="object-fit:cover;border-radius:.5rem;flex:0 0 44px;">
              <div style="flex:1;min-width:0;">
                <strong><?= esc($item['laboratory_name']) ?></strong>
                <div class="text-xs text-muted-foreground"><?= esc($item['room_code'] . ' - ' . $item['room_name']) ?></div>
                <?php if (! empty($item['notes'])): ?><div class="text-xs" style="margin-top:.25rem;"><?= esc($item['notes']) ?></div><?php endif; ?>
              </div>
              <?php if ($editable): ?>
              <form action="<?= base_url('peminjaman/lab-loans/items/' . $proposal['uuid'] . '/remove/' . $item['uuid']) ?>" method="post" onsubmit="return confirm('Hapus ruangan ini dari cart?')">
                <?= csrf_field() ?>
                <button type="submit" class="button button--ghost button--danger button--icon-only button--sm" title="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M20 6H4m12 0v12a1 1 0 0 1-1 1H9a1 1 0 0 1-1-1V6m-2 0 .5-2h11l.5 2" /></svg>
                </button>
              </form>
              <?php endif; ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
        <?php if ($editable && ! empty($cart)): ?>
        <div class="card__body" style="border-top:1px solid var(--color-border);">
          <a href="<?= base_url('peminjaman/lab-loans/confirm/' . $proposal['uuid']) ?>" class="button button--primary w-full">
              Selanjutnya
              <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M5 12h14m-6-6 6 6-6 6" /></svg>
          </a>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<div class="loan-items__lightbox" id="labPhotoLightbox" role="dialog" aria-modal="true" aria-labelledby="labPhotoCaption">
  <div class="loan-items__lightbox-panel">
    <div class="card__header">
      <span class="card__title" id="labPhotoCaption"></span>
      <div class="card__action">
        <button type="button" class="button button--ghost button--neutral button--icon-only button--sm" data-lightbox-close aria-label="Tutup">
          <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.5" d="M6 6l12 12M18 6 6 18" /></svg>
        </button>
      </div>
    </div>
    <img id="labPhotoImage" src="" alt="">
  </div>
</div>

<script>
  (function () {
    const lightbox = document.getElementById('labPhotoLightbox');
    const image = document.getElementById('labPhotoImage');
    const caption = document.getElementById('labPhotoCaption');

    const close = () => lightbox.removeAttribute('open');

    document.querySelectorAll('.loan-items__zoom').forEach((button) => {
      button.addEventListener('click', () => {
        image.src = button.dataset.photo;
        image.alt = button.dataset.caption;
        caption.textContent = button.dataset.caption;
        lightbox.setAttribute('open', '');
      });
    });

    lightbox.addEventListener('click', (event) => {
      if (event.target === lightbox || event.target.closest('[data-lightbox-close]')) {
        close();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        close();
      }
    });
  })();
</script>