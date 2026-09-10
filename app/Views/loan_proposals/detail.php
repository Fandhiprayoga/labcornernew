<?php
/** @var array $proposal */
/** @var array $items */
/** @var array $history */
$approvalMode = $approvalMode ?? false;
$statusLabels = ['draft' => 'Draft', 'submitted' => 'Menunggu Approval Laboran', 'laboran_approved' => 'Menunggu Approval Kepala Lab', 'rejected' => 'Ditolak', 'approved' => 'Disetujui', 'completed' => 'Selesai'];
$statusColors = ['draft' => 'secondary', 'submitted' => 'warning', 'laboran_approved' => 'info', 'rejected' => 'danger', 'approved' => 'success', 'completed' => 'primary'];
$fmt = static fn (string $value): string => date('d M Y H:i', strtotime($value));
?>
<style>
  .proposal-detail__tabs { display:flex; gap:.25rem; overflow-x:auto; border-bottom:1px solid var(--color-border); }
  .proposal-detail__tab { border:0; border-bottom:2px solid transparent; background:transparent; color:var(--color-muted-foreground); padding:.75rem 1rem; cursor:pointer; white-space:nowrap; }
  .proposal-detail__tab[aria-selected="true"] { color:var(--color-foreground); border-bottom-color:var(--color-primary); font-weight:600; }
  .proposal-detail__panel { padding-top:1rem; }
  .proposal-detail__panel[hidden] { display:none; }
  .proposal-detail__summary { display:grid; grid-template-columns:repeat(1,minmax(0,1fr)); gap:1rem; }
  .proposal-detail__lab-photo { width:4rem; height:3rem; object-fit:cover; border-radius:.5rem; display:block; }
  .proposal-approval__layout { display:grid; grid-template-columns:minmax(0,2fr) minmax(18rem,1fr); gap:1rem; align-items:start; }
  .proposal-approval__decision .card__body { display:flex; flex-direction:column; gap:1rem; }
  .proposal-approval__illustration { width:8rem; height:8rem; object-fit:contain; align-self:center; }
  .proposal-approval__decision .proposal-approval__actions { display:flex; gap:.5rem; }
  .proposal-approval__decision .proposal-approval__actions .button { flex:1; }
  @media (min-width:40rem) { .proposal-detail__summary { grid-template-columns:repeat(2,minmax(0,1fr)); } }
  @media (max-width:55rem) { .proposal-approval__layout { grid-template-columns:1fr; } }
</style>
<div class="page__section flex flex-col gap-4">
  <?php if ($approvalMode): ?><div class="proposal-approval__layout"><?php endif; ?>
  <div class="card">
    <div class="card__header">
      <div><span class="card__title"><?= esc($proposal['event_name']) ?></span><div class="text-xs text-muted-foreground"><?= $approvalMode ? 'Detail Approval Proposal' : 'Detail Proposal Peminjaman' ?></div></div>
      <div class="card__action"><span class="badge badge--soft badge--<?= esc($statusColors[$proposal['status']] ?? 'secondary') ?>"><?= esc($statusLabels[$proposal['status']] ?? ucfirst($proposal['status'])) ?></span><a href="<?= base_url($approvalMode ? 'peminjaman/lab-loans-approval' : 'peminjaman/lab-loans') ?>" class="button button--outline button--neutral button--sm">Kembali</a></div>
    </div>
    <div class="card__body">
      <div class="proposal-detail__tabs" role="tablist" aria-label="Detail proposal">
        <button type="button" class="proposal-detail__tab" role="tab" aria-selected="true" aria-controls="proposal-info" data-tab="proposal-info">Detail Proposal</button>
        <button type="button" class="proposal-detail__tab" role="tab" aria-selected="false" aria-controls="proposal-items" data-tab="proposal-items">Item yang Dipinjam</button>
        <button type="button" class="proposal-detail__tab" role="tab" aria-selected="false" aria-controls="proposal-history" data-tab="proposal-history">History Perubahan Status</button>
      </div>
      <section class="proposal-detail__panel" id="proposal-info" role="tabpanel">
        <div class="proposal-detail__summary">
          <div><div class="text-xs text-muted-foreground">Pemohon</div><strong><?= esc($proposal['full_name']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['identity_number']) ?></div></div>
          <div><div class="text-xs text-muted-foreground">Kontak</div><strong><?= esc($proposal['phone']) ?></strong><div class="text-xs text-muted-foreground"><?= esc($proposal['email']) ?></div></div>
          <div><div class="text-xs text-muted-foreground">Tanggal Proposal</div><strong><?= esc(date('d M Y', strtotime($proposal['proposal_date']))) ?></strong></div>
          <div><div class="text-xs text-muted-foreground">Waktu Kegiatan</div><strong><?= esc($fmt($proposal['event_start'])) ?></strong><div class="text-xs text-muted-foreground">sampai <?= esc($fmt($proposal['event_end'])) ?></div></div>
        </div>
      </section>
      <section class="proposal-detail__panel" id="proposal-items" role="tabpanel" hidden>
        <?php if (empty($items)): ?><p class="text-sm text-muted-foreground">Belum ada item yang dipinjam.</p><?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Gambar</th><th>Laboratorium</th><th>Ruangan</th><th>Gedung</th><th>Catatan</th></tr></thead><tbody>
          <?php foreach ($items as $item): ?><tr><td><img class="proposal-detail__lab-photo" src="<?= esc(base_url($item['laboratory_photo'] ?: 'assets/images/default-laboratory.svg'), 'attr') ?>" alt="Foto <?= esc($item['laboratory_name'], 'attr') ?>"></td><td><strong><?= esc($item['laboratory_name']) ?></strong></td><td><?= esc($item['room_code'] . ' - ' . $item['room_name']) ?></td><td><?= esc($item['building'] ?: '-') ?></td><td><?= esc($item['notes'] ?: '-') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
      </section>
      <section class="proposal-detail__panel" id="proposal-history" role="tabpanel" hidden>
        <?php if (empty($history)): ?><p class="text-sm text-muted-foreground">Belum ada riwayat perubahan status.</p><?php else: ?>
        <div class="table-responsive"><table class="table"><thead><tr><th>Waktu</th><th>Perubahan Status</th><th>Oleh</th><th>Keterangan</th></tr></thead><tbody>
          <?php foreach ($history as $entry): ?><tr><td><?= esc($fmt($entry['created_at'])) ?></td><td><?= esc($statusLabels[$entry['from_status']] ?? 'Status awal') ?> &rarr; <strong><?= esc($statusLabels[$entry['to_status']] ?? ucfirst($entry['to_status'])) ?></strong></td><td><?= esc($entry['changed_by_name'] ?: '-') ?></td><td><?= esc($entry['note'] ?: '-') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
      </section>
    </div>
  </div>
  <?php if ($approvalMode): ?>
  <div class="card proposal-approval__decision">
    <div class="card__body">
      <img class="proposal-approval__illustration" src="<?= esc(base_url('assets/img/approval-success.svg'), 'attr') ?>" alt="Ilustrasi persetujuan proposal">
      <div><strong>Keputusan Approval</strong><div class="text-sm text-muted-foreground">Tinjau detail proposal sebelum mengambil keputusan.</div></div>
      <div class="proposal-approval__actions">
        <button type="button" class="button button--danger" onclick="openApprovalDialog('rejectConfirm', '<?= esc(base_url('peminjaman/lab-loans-approval/' . $proposal['uuid'] . '/reject'), 'js') ?>', '<?= esc($proposal['event_name'], 'js') ?>')">Tolak</button>
        <button type="button" class="button button--success" onclick="openApprovalDialog('approveConfirm', '<?= esc(base_url('peminjaman/lab-loans-approval/' . $proposal['uuid'] . '/approve'), 'js') ?>', '<?= esc($proposal['event_name'], 'js') ?>')">Setujui</button>
      </div>
    </div>
  </div>
  </div>
  <?php endif; ?>
</div>
<?php if ($approvalMode): ?>
<?= view('loan_proposals/_approval_dialogs') ?>
<?php endif; ?>
<script>
  (() => {
    const tabs = document.querySelectorAll('[data-tab]');
    const panels = document.querySelectorAll('.proposal-detail__panel');
    tabs.forEach((tab) => tab.addEventListener('click', () => {
      tabs.forEach((item) => item.setAttribute('aria-selected', item === tab ? 'true' : 'false'));
      panels.forEach((panel) => { panel.hidden = panel.id !== tab.dataset.tab; });
    }));
  })();
</script>
