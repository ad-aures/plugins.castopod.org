<?php
use CodeIgniter\View\View;

/**
 * @var View
 */

?>

<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.login') ?> <?= $this->endSection() ?>
<?= $this->section('pageTitle') ?><?= lang('Auth.login') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
    <form action="<?= url_to('login') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="flex flex-col">
            <label class="font-bold text-skin-muted text-sm" for="email"><?= lang(
                'Auth.email',
            ) ?></label>
            <input type="email" id="email" name="email" inputmode="email" autocomplete="email" placeholder="<?= lang(
                'Auth.email',
            ) ?>" value="<?= old(
                'email',
            ) ?>" required>
        </div>

        <div class="flex flex-col mt-2">
            <label class="font-bold text-skin-muted text-sm" for="password"><?= lang('Auth.password') ?></label>
            <input type="password" id="password" name="password" inputmode="text" autocomplete="current-password" placeholder="<?= lang(
                'Auth.password',
            ) ?>" required>
        </div>
            
        <!-- Remember me -->
        <?php if (setting('Auth.sessionConfig')['allowRemembering'] /** @phpstan-ignore offsetAccess.nonOffsetAccessible */): ?>
            <div class="mt-2">
                <label class="form-check-label">
                    <input type="checkbox" name="remember" class="text-accent" <?php if (old('remember')): ?> checked<?php endif ?>>
                    <?= lang('Auth.rememberMe') ?>
                </label>
            </div>
        <?php endif; ?>

        <button type="submit" class="my-6 px-4 py-2 w-full btn-primary"><?= lang('Auth.login') ?></button>

        <div class="text-sm text-center leading-loose">
            <?php if (setting('Auth.allowMagicLinkLogins')) : ?>
                <p class="text-center"><?= lang('Auth.forgotPassword') ?> <a href="<?= url_to(
                    'magic-link',
                ) ?>" class="decoration-2 decoration-primary underline hover:no-underline"><?= lang(
                    'Auth.useMagicLink',
                ) ?></a></p>
            <?php endif ?>
            
            <?php if (setting('Auth.allowRegistration')) : ?>
                <p class="text-center"><?= lang('Auth.needAccount') ?> <a href="<?= url_to(
                    'register',
                ) ?>" class="decoration-2 decoration-primary underline hover:no-underline"><?= lang(
                    'Auth.register',
                ) ?></a></p>
            <?php endif ?>
        </div>
    </form>
<?= $this->endSection() ?>
