<?php

use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('info/_layout_edit') ?>

<?php $this->section('content') ?>
    <form method="POST" action="<?= route_to(
        'plugin-action',
        $plugin->key,
    ) ?>" class="flex flex-col gap-4 max-w-xl">
        <div class="flex flex-col">
            <label for="repository_url" class="font-semibold"><?= lang('Plugin.submitForm.repositoryUrl') ?></label>
            <input type="url" id="repository_url" name="repository_url" placeholder="https://github.com/acme/foo.git" class="border-0 ring-2 ring-contrast focus:ring-4 w-full transition" value="<?= $plugin->repository_url ?>">
        </div>
        <div class="flex flex-col">
            <label for="manifest_root" class="font-semibold"><?= lang('Plugin.submitForm.manifestRoot') ?></label>
            <input type="text" id="manifest_root" name="manifest_root" placeholder="/" class="border-0 ring-2 ring-contrast focus:ring-4 w-full transition" value="<?= $plugin->manifest_root ?>">
        </div>

        <?= altcha_widget(['floating']) ?>
        <button class="self-start mt-2 px-4 py-2 btn-primary" name="action" value="edit-repository" type="submit"><?= lang(
            'Common.forms.save',
        ) ?></button>
    </form>
<?php $this->endSection() ?>
