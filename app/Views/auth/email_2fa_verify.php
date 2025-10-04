<?php
use CodeIgniter\View\View;

/**
 * @var View $this
 */

?>

<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.email2FATitle') ?> <?= $this->endSection() ?>
<?= $this->section('pageTitle') ?><?= lang('Auth.emailEnterCode') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
<p><?= lang('Auth.emailConfirmCode') ?></p>
<form action="<?= url_to('auth-action-verify') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Code -->
    <input type="number" class="mt-2" name="token" placeholder="000000" inputmode="numeric" pattern="[0-9]*" autocomplete="one-time-code" required="required">

    <?= altcha_widget(['floating']) ?>

    <button type="submit" class="mt-4 px-4 py-2 w-full btn-primary"><?= lang(
        'Auth.confirm',
    ) ?></button>
</form>
<?= $this->endSection() ?>
