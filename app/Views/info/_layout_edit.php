<?php

use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('_layout') ?>

<?php $this->section('headerLeft') ?><h1 class="font-display font-bold text-4xl"><?= lang('Plugin.editTitle', [
    'pluginKey' => $plugin->key,
]) ?></h1><?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="container">
        <nav class="flex -mt-11 border-2 divide-border-contrast divide-x-2 w-fit" hx-boost="true">
            <?= $this->include('info/_tabs_edit') ?>
        </nav>
        <div class="py-6">
            <?= $this->renderSection('content') ?>
        </div>
    </div>
<?php $this->endSection() ?>
