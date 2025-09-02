<?php

use CodeIgniter\Pager\PagerRenderer;

/** @var PagerRenderer $pager */
$pager->setSurroundCount(2);
?>

<nav aria-label="<?= lang('Pager.pageNavigation') ?>" class="flex px-2 py-4 text-center">
    <?php if ($pager->hasPrevious()) : ?>
        <div class="flex items-center gap-2">
            <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>" class="px-4 py-2">
                <span aria-hidden="true"><?= lang('Pager.first') ?></span>
            </a>
            <a href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>" class="px-4 py-2">
                <span aria-hidden="true"><?= lang('Pager.previous') ?></span>
            </a>
        </div>
    <?php endif ?>

    <div class="flex gap-4 mx-auto">
    <?php foreach ($pager->links() as $link): ?>
            <a href="<?= $link['uri'] ?>" class="grid place-items-center font-semibold size-12 <?= $link['active'] ? ' btn-primary' : '' ?>">
                <?= $link['title'] ?>
            </a>
            <?php endforeach ?>
    </div>

    <?php if ($pager->hasNext()) : ?>
        <div class="flex items-center gap-2">
            <a href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>" class="px-4 py-2">
                <span aria-hidden="true"><?= lang('Pager.next') ?></span>
            </a>
            <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>" class="px-4 py-2">
                <span aria-hidden="true"><?= lang('Pager.last') ?></span>
            </a>
        </div>
    <?php endif ?>
</nav>
