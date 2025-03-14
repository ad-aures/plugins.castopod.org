<?php
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
?>

<?php $this->extend('_layout') ?>

<?php $this->section('title') ?>Submit a plugin<?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="mx-auto py-6 container">
        <form method="POST" action="<?= route_to(
            'plugin-submit',
        ) ?>" class="flex flex-col gap-4 max-w-xl" hx-swap="none" hx-boost="true">
            <div class="flex flex-col">
                <label for="repository_url" class="font-semibold text-brand-100">Repository URL</label>
                <input type="url" id="repository_url" name="repository_url" placeholder="https://github.com/acme/foo.git" class="bg-brand-950 border-0 ring ring-brand-800 focus:ring-2 focus:ring-brand-500 w-full text-white">
            </div>
            <div class="flex flex-col">
                <label for="repository_folder" class="font-semibold text-brand-100">Folder</label>
                <input type="text" id="repository_folder" name="repository_folder" placeholder="/" class="bg-brand-950 border-0 ring ring-brand-800 focus:ring-2 focus:ring-brand-500 w-full text-white">
            </div>
            <button class="self-start bg-white mt-2 px-4 py-2">Submit plugin!</button>
        </form>
    </div>
<?php $this->endSection() ?>
