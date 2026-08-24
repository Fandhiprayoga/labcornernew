<style>
  .ss-wrap { position: relative; }
  .ss-native-select {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }
  .ss-menu {
    position: absolute;
    z-index: 40;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    max-height: 240px;
    overflow-y: auto;
    background: var(--color-surface, #fff);
    border: 1px solid var(--color-border);
    border-radius: var(--radius-md, 6px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  }
  .ss-menu ul { list-style: none; margin: 0; padding: 4px; }
  .ss-menu li {
    padding: 0.5rem 0.65rem;
    border-radius: var(--radius-sm, 4px);
    cursor: pointer;
    font-size: 0.875rem;
  }
  .ss-menu li:hover,
  .ss-menu li[aria-selected="true"] {
    background: var(--color-accent, rgba(0, 0, 0, 0.06));
  }
  .ss-menu li.ss-empty {
    cursor: default;
    color: var(--color-muted-foreground);
  }
  .ss-menu li.ss-empty:hover { background: transparent; }
</style>
<script src="<?= base_url('assets/js/searchable-select.js') ?>"></script>
