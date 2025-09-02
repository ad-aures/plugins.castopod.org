<?= $this->extend(config('Auth')->views['layout']) ?>

<?= $this->section('title') ?><?= lang('Auth.register') ?> <?= $this->endSection() ?>
<?= $this->section('pageTitle') ?><?= lang('Auth.register') ?> <?= $this->endSection() ?>

<?= $this->section('main') ?>
<form action="<?= url_to('register') ?>" method="post">
    <?= csrf_field() ?>

    <!-- Email -->
    <div class="flex flex-col">
        <label class="font-bold text-skin-muted text-sm" for="email"><?= lang('Auth.email') ?></label>
        <input type="email" id="email" name="email" inputmode="email" autocomplete="email" value="<?= old('email') ?>" required>
    </div>

    <!-- Username -->
    <div class="flex flex-col mt-2">
        <label class="font-bold text-skin-muted text-sm" for="username"><?= lang('Auth.username') ?></label>
        <input type="text" id="username" name="username" inputmode="text" autocomplete="username" value="<?= old('username') ?>" required>
    </div>

    <!-- Password -->
    <div class="flex flex-col mt-4">
        <label class="font-bold text-skin-muted text-sm" for="password"><?= lang('Auth.password') ?></label>
        <input type="password" id="password" name="password" inputmode="text" autocomplete="new-password" required>
    </div>

    <!-- Password (Again) -->
    <div class="flex flex-col mt-2">
        <label class="font-bold text-skin-muted text-sm" for="password_confirm"><?= lang('Auth.passwordConfirm') ?></label>
        <input type="password" id="password_confirm" name="password_confirm" inputmode="text" autocomplete="new-password" required>
    </div>

    <button type="submit" class="my-6 px-4 py-2 w-full btn-primary"><?= lang('Auth.register') ?></button>

    <p class="text-sm text-center"><?= lang('Auth.haveAccount') ?> <a href="<?= url_to('login') ?>" class="decoration-2 decoration-primary underline hover:no-underline"><?= lang('Auth.login') ?></a></p>

</form>
<?= $this->endSection() ?>
