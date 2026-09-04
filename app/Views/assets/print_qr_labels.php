<?php /** @var array $assets */ ?>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; color: #111827; font-family: Arial, sans-serif; }
  .print-toolbar { display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #d1d5db; }
  .print-toolbar h1 { margin: 0; font-size: 18px; }
  .print-toolbar button { border: 0; border-radius: 4px; background: #0f766e; color: #fff; cursor: pointer; font-size: 14px; padding: 9px 14px; }
  .labels { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8mm; padding: 8mm; }
  .label { min-height: 54mm; border: 1px dashed #9ca3af; display: grid; grid-template-columns: 38mm 1fr; align-items: center; gap: 3mm; padding: 3mm; break-inside: avoid; }
  .label__qr { width: 36mm; height: 36mm; }
  .label__code { font-size: 12pt; font-weight: 700; overflow-wrap: anywhere; }
  .label__name { margin-top: 2mm; font-size: 9pt; line-height: 1.25; }
  .label__location { margin-top: 2mm; color: #4b5563; font-size: 8pt; line-height: 1.25; }
  @media print {
    @page { margin: 8mm; size: A4 portrait; }
    .print-toolbar { display: none; }
    .labels { gap: 5mm; padding: 0; }
    .label { min-height: 52mm; }
  }
</style>
<div class="print-toolbar">
  <h1>Label QR Asset (<?= count($assets) ?>)</h1>
  <button type="button" onclick="window.print()">Cetak</button>
</div>
<main class="labels">
  <?php foreach ($assets as $asset): ?>
  <article class="label">
    <img class="label__qr" src="<?= esc($asset['qr_code'], 'attr') ?>" alt="QR <?= esc($asset['asset_code']) ?>">
    <div>
      <div class="label__code"><?= esc($asset['asset_code']) ?></div>
      <div class="label__name"><?= esc($asset['name']) ?></div>
      <div class="label__location"><?= esc($asset['laboratory_name']) ?><?= ! empty($asset['room_code']) ? ' - ' . esc($asset['room_code']) : '' ?></div>
    </div>
  </article>
  <?php endforeach; ?>
</main>