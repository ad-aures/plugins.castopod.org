<?php
use CodeIgniter\View\View;

/**
 * @var View $this
 */

?>

<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.emailActivateTitle') ?> <?= $this->endSection() ?>
<?= $this->section('pageTitle') ?><?= lang('Auth.emailActivateTitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
<p><?= lang('Auth.emailActivateBody') ?></p>

<form action="<?= url_to('auth-action-verify') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Code -->
    <label class="font-bold text-skin-muted text-sm" for="token"><?= lang('Auth.token') ?></label>
    <input type="text" id="token" name="token" placeholder="000000" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" value="<?= old(
        'token',
    ) ?>" required>

    <?= altcha_widget(['floating']) ?>
    <button type="submit" class="mt-4 px-4 py-2 w-full btn-primary"><?= lang('Auth.send') ?></button>
</form>
<?= $this->endSection() ?>
