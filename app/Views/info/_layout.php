<?php
use App\Entities\Plugin;
use App\Entities\Version;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
/** @var Version $currentVersion */
?>

<?php $this->extend('_layout') ?>

<?php $this->section('headerRight') ?>
    <h1 class="flex flex-col font-bold"><span><?= $plugin->vendor ?>/</span><span class="-mt-2 font-display text-4xl"><?= $plugin->name ?></span></h1>
    <div class="flex items-center text-brand-200 text-sm">
        <span class="font-mono"><?= $currentVersion->tag ?></span>
        <span class="mx-2">•</span>
        <span class="">Published <?= relative_time($currentVersion->published_at) ?></span>
    </div>
    <ul class="flex flex-wrap gap-2 mt-4"><?php foreach ($plugin->categories as $category): ?>
        <li class="bg-brand-200 px-2 py-0.5 font-bold text-brand-950 text-sm"><a href="<?= route_to(
            'search',
        ) . '?categories[]=' . $category->value ?>">#<?= $category->value ?></a></li>
    <?php endforeach; ?></ul>
<?php $this->endSection() ?>

<?php $this->section('main') ?>
<div class="flex lg:flex-row flex-col mx-auto pb-8 text-white grow-1 container">
    <section class="flex flex-col items-start bg-brand-950 text-black grow-1">
        <nav class="flex items-center bg-brand-950 -mt-10 border-x border-t border-brand-950 divide-x divide-brand-900" hx-boost="true">
            <?= $this->include('info/_tabs') ?>
        </nav>
        <div class="p-4 lg:p-8 w-full text-white">
            <?= $this->renderSection('content') ?>
        </div>
    </section>
    <aside class="flex flex-col mt-4 lg:mt-8 px-4 lg:px-6 lg:w-[26rem]">
        <?= $this->include('info/_metadata') ?>
    </aside>
</div>
<?php $this->endSection() ?>