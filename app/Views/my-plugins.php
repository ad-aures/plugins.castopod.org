<?php

use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin[] $plugins */
?>

<?php $this->extend('_layout') ?>

<?php $this->section(
    'headerLeft',
) ?><h1 class="font-display font-bold text-5xl"><?= lang('Common.myPlugins') ?></h1><?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="items-start gap-8 grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] -mt-28 py-8 container grow">
        <?php
        $isFirstOfficialPlugin = true;
foreach ($plugins as $key => $plugin) {
    echo view('_plugin', [
        'key'                   => $key,
        'plugin'                => $plugin,
        'isFirstOfficialPlugin' => $plugin->is_official && $isFirstOfficialPlugin,
    ]);

    if ($plugin->is_official) {
        $isFirstOfficialPlugin = false;
    }
} ?>
    </div>
<?php $this->endSection() ?>