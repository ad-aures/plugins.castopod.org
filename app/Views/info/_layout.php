<?php
use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('_layout') ?>

<?php $this->section('headerRight') ?>
<div class="flex flex-col">
    <h1 class="flex flex-col font-bold"><span><?= $plugin->vendor ?>/</span><span class="-mt-2 font-display text-4xl"><?= $plugin->name ?></span></h1>
    <div class="flex items-center text-brand-200 text-sm">
        <span class="font-mono"><?= $plugin->selected_version->tag ?></span>
        <span class="mx-2">•</span>
        <span class="">Published <?= relative_time($plugin->selected_version->published_at) ?></span>
    </div>
    <ul class="flex flex-wrap gap-2 mt-4"><?php foreach ($plugin->categories as $category): ?>
        <li class="bg-brand-200 px-2 py-0.5 font-bold text-brand-950 text-sm"><a href="<?= route_to(
            'search',
        ) . '?categories[]=' . $category->value ?>">#<?= $category->value ?></a></li>
    <?php endforeach; ?></ul>
</div>
<?php $this->endSection() ?>

<?php $this->section('headerLeft') ?>
    <?php if ($plugin->is_updating): ?>
        <div class="inline-flex items-center gap-x-2 bg-orange-800 px-4 py-2"><?= icon(
            'loop-left-fill',
            [
                'class' => 'animate-spin',
            ],
        ) ?>Updating…</div>
    <?php else: ?>
        <form action="<?= route_to('plugin-action', $plugin->key) ?>" method="POST">
            <button class="inline-flex justify-center items-center gap-x-2 bg-orange-700 px-4 py-2 cursor-pointer shrink-0" name="action" value="update" type="submit"><?= icon(
                'loop-left-fill',
            ) ?>Update plugin</button>
        </form>
    <?php endif; ?>
<?php $this->endSection(); ?>

<?php $this->section('main') ?>
<div class="flex lg:flex-row flex-col text-white grow-1 container">
    <section class="flex flex-col items-start bg-brand-950 text-black grow-1">
        <nav class="flex items-center bg-brand-950 -mt-10 border-x border-t border-brand-950 divide-x divide-brand-900" hx-boost="true">
            <?= $this->include('info/_tabs') ?>
        </nav>
        <div class="p-4 lg:p-8 w-full text-white">
            <?= $this->renderSection('content') ?>
        </div>
    </section>
    <aside class="flex flex-col starting:opacity-0 mt-4 lg:mt-8 px-4 lg:px-6 lg:w-[26rem]">
        <?= $this->include('info/_metadata') ?>
    </aside>
</div>
<div class="mt-8 pb-6 container">
    <h2 class="font-display text-white text-2xl">⚠️ Danger zone</h2>
    <form action="<?= route_to('plugin-action', $plugin->key) ?>" method="POST">
        <button class="inline-flex justify-center items-center gap-x-2 bg-red-800 mt-4 px-4 py-2 text-white cursor-pointer" name="action" value="delete" type="submit" title="delete plugin"><?= icon(
            'delete-bin-fill',
        ) ?>Delete plugin</button>
    </form>
</div>
<?php $this->endSection() ?>