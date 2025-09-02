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
    ) ?>" class="flex flex-col gap-4 max-w-xl" hx-swap="none" hx-boost="true">
        <div class="flex flex-col">
            <label for="repository_url" class="font-semibold">Repository URL</label>
            <input type="url" id="repository_url" name="repository_url" placeholder="https://github.com/acme/foo.git" class="border-0 ring-2 ring-contrast focus:ring-4 w-full transition" value="<?= $plugin->repository_url ?>">
        </div>
        <div class="flex flex-col">
            <label for="manifest_root" class="font-semibold">Manifest root</label>
            <input type="text" id="manifest_root" name="manifest_root" placeholder="/" class="border-0 ring-2 ring-contrast focus:ring-4 w-full transition" value="<?= $plugin->manifest_root ?>">
        </div>
        <button class="self-start mt-2 px-4 py-2 btn-primary" name="action" value="edit-repository" type="submit">Save</button>
    </form>
<?php $this->endSection() ?>
