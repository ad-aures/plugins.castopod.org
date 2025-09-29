<?php

use CodeIgniter\Shield\Entities\User;
use CodeIgniter\View\View;

/**
 * @var User $user
 * @var View $this
 */

?>

<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.email2FATitle') ?> <?= $this->endSection() ?>
<?= $this->section('pageTitle') ?><?= lang('Auth.email2FATitle') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
<form action="<?= url_to('auth-action-handle') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Email -->
    <input type="email" class="mb-2" name="email" inputmode="email" autocomplete="email" placeholder="<?= lang(
        'Auth.email',
    ) ?>" value="<?= old(
        'email',
        $user->email,
    ) ?>" required>

    <?= altcha_widget(['floating']) ?>
    <button type="submit" class="mt-4 px-4 py-2 w-full btn-primary"><?= lang('Auth.send') ?></button>
</form>
<?= $this->endSection() ?>
