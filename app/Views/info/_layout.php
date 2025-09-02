<?php
use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
/** @var bool $canUpdate */
/** @var bool $canDelete */
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

<?php if ($canUpdate): ?>
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
                <button class="inline-flex justify-center items-center gap-x-2 bg-brand-50 px-4 py-2 font-bold text-brand-950 cursor-pointer shrink-0" name="action" value="update" type="submit"><?= icon(
                    'loop-left-fill',
                ) ?>Update plugin</button>
            </form>
        <?php endif; ?>
<?php $this->endSection(); ?>
<?php endif; ?>

<?php $this->section('main') ?>
<div class="flex lg:flex-row flex-col pb-8 grow-1 container">
    <section class="flex flex-col items-start -mt-11 grow-1">
        <nav class="flex bg-surface-base border-x-2 divide-border-contrast border-t-2 divide-x-2 ring-contrast" hx-boost="true">
            <?= $this->include('info/_tabs') ?>
        </nav>
        <div class="bg-surface-bright p-4 lg:p-8 border-2 border-contrast w-full h-full">
            <?= $this->renderSection('content') ?>
        </div>
    </section>
    <aside class="flex flex-col starting:opacity-0 px-4 lg:px-6 py-4 lg:py-8 lg:w-[26rem]">
        <?= $this->include('info/_metadata') ?>
    </aside>
</div>
<?php if ($canDelete): ?>
<hr class="my-8 container">
<div class="pb-6 container">
    <h2 class="font-display text-2xl">⚠️ Danger zone</h2>
    <form action="<?= route_to('plugin-action', $plugin->key) ?>" method="POST">
        <button class="mt-4 px-4 py-2 btn-danger" name="action" value="delete" type="submit" title="delete plugin"><?= icon(
            'delete-bin-fill', ['class' => 'text-on-error/75']
        ) ?>Delete plugin</button>
    </form>
</div>
<?php endif; ?>
<?php $this->endSection() ?>