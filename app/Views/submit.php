<?php
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
?>

<?php $this->extend('_layout') ?>

<?php $this->section(
    'headerRight',
) ?><h1 class="font-display font-bold text-5xl">Submit a plugin</h1><?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="py-6 container">
        <form method="POST" action="<?= route_to(
            'plugin-index',
        ) ?>" class="flex flex-col gap-4 max-w-xl" hx-swap="none" hx-boost="true">
            <div class="flex flex-col">
                <label for="repository_url" class="font-semibold">Repository URL</label>
                <input type="url" id="repository_url" name="repository_url" placeholder="https://github.com/acme/foo.git" class="border-0 ring-2 ring-contrast focus:ring-4 w-full transition">
            </div>
            <div class="flex flex-col">
                <label for="manifest_root" class="font-semibold">Folder</label>
                <input type="text" id="manifest_root" name="manifest_root" placeholder="/" class="border-0 ring-2 ring-contrast focus:ring-4 w-full transition">
            </div>
            <button class="self-start mt-2 px-4 py-2 btn-primary">Submit plugin!</button>
        </form>
    </div>
<?php $this->endSection() ?>
