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
    <form action="<?= url_to('magic-link') ?>" method="post">
        <?= csrf_field() ?>

        <!-- Email -->
        <div class="flex flex-col">
            <label class="font-bold text-skin-muted text-sm" for="email"><?= lang(
                'Auth.email',
            ) ?></label>
            <input type="email" id="email" name="email" autocomplete="email" placeholder="<?= lang(
                'Auth.email',
            ) ?>" value="<?= old(
                'email',
                auth()
                    ->user()
                    ->email ?? null,
            ) ?>" required>
        </div>

        <button type="submit" class="my-6 px-4 py-2 w-full btn-primary"><?= lang('Auth.send') ?></button>

        <p class="text-center"><a href="<?= url_to(
            'login',
        ) ?>" class="inline-flex items-center gap-x-2 decoration-2 decoration-primary underline hover:no-underline"><?= icon(
            'arrow-left-long-line',
        ) . lang(
            'Auth.backToLogin',
        ) /** @phpstan-ignore binaryOp.invalid */ ?></a></p>
    </form>

<?= $this->endSection() ?>
