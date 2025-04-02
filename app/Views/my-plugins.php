<?php

use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin[] $plugins */
?>

<?php $this->extend('_layout') ?>

<?php $this->section(
    'headerRight',
) ?><h1 class="font-display font-bold text-5xl">My plugins</h1><?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="items-start gap-8 grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] py-8 lg:border-0 border-t border-brand-950 container grow">
        <?php
        foreach ($plugins as $key => $plugin):
            echo view('_plugin', [
                'key'    => $key,
                'plugin' => $plugin,
            ]);
        endforeach; ?>
    </div>
<?php $this->endSection() ?>