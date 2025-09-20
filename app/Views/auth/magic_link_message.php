<?php
use CodeIgniter\View\View;

/**
 * @var View
 */

?>

<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.useMagicLink') ?> <?= $this->endSection() ?>
<?= $this->section('pageTitle') ?><?= lang('Auth.useMagicLink') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
<p class="font-bold"><?= lang('Auth.checkYourEmail') ?></p>
<p><?= lang(
    'Auth.magicLinkDetails',
    [setting('Auth.magicLinkLifetime') / 60 /** @phpstan-ignore binaryOp.invalid */],
) ?></p>
<?= $this->endSection() ?>
