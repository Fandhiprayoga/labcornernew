<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * @var PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>
<nav aria-label="<?= lang('Pager.pageNavigation') ?>" style="display:flex;align-items:center;gap:.25rem;flex-wrap:wrap;">
  <?php if ($pager->hasPrevious()): ?>
    <a href="<?= $pager->getFirst() ?>" class="button button--outline button--sm" aria-label="<?= lang('Pager.first') ?>">&laquo;</a>
    <a href="<?= $pager->getPrevious() ?>" class="button button--outline button--sm" aria-label="<?= lang('Pager.previous') ?>">&lsaquo;</a>
  <?php endif ?>

  <?php foreach ($pager->links() as $link): ?>
    <a href="<?= $link['uri'] ?>" class="button button--sm <?= $link['active'] ? 'button--primary' : 'button--outline' ?>"<?= $link['active'] ? ' aria-current="page"' : '' ?>><?= $link['title'] ?></a>
  <?php endforeach ?>

  <?php if ($pager->hasNext()): ?>
    <a href="<?= $pager->getNext() ?>" class="button button--outline button--sm" aria-label="<?= lang('Pager.next') ?>">&rsaquo;</a>
    <a href="<?= $pager->getLast() ?>" class="button button--outline button--sm" aria-label="<?= lang('Pager.last') ?>">&raquo;</a>
  <?php endif ?>
</nav>
