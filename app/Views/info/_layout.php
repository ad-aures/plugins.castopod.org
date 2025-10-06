<?php
use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
/** @var bool $canUpdate */
/** @var bool $canDelete */
?>

<?php $this->extend('_layout') ?>

<?php $this->section('headerLeft') ?>
<div class="flex flex-col">
    <h1 class="font-bold select-all"><span class="block z-10 relative -mb-2"><?= $plugin->vendor ?>/</span><span class="inline font-display text-4xl align-middle"><?= $plugin->name ?></span><?php if ($plugin->is_official): ?><span class="inline top-0.5 relative ml-2 align-middle" tabindex="0" title="<?= lang(
        'Plugin.official',
    ) ?>" data-tooltip="bottom"><?= icon(
        'verified-badge-fill',
        [
            'class' => 'inline text-brand-50 text-2xl',

        ],
    ) ?></span><?php endif; ?></h1>
    <div class="flex items-center text-brand-200 text-sm">
        <span class="font-mono select-all"><?= $plugin->selected_version->tag ?></span>
        <span class="mx-2">•</span>
        <span class=""><?= lang('Plugin.published', [
            'relativeTime' => relative_time($plugin->selected_version->published_at),
        ]) ?></span>
    </div>
    <ul class="flex flex-wrap gap-2 mt-4"><?php foreach ($plugin->categories as $category): ?>
        <li><a href="<?= route_to(
            'search',
        ) . '?categories[]=' . $category->value ?>" class="bg-brand-200 px-2 py-0.5 font-bold text-brand-950 text-sm">#<?= $category->value ?></a></li>
    <?php endforeach; ?></ul>
</div>
<?php $this->endSection() ?>

<?php $this->section('headerRight') ?>
<div class="flex items-center gap-x-2">
    <?php if ($canUpdate): ?>
        <?php if ($plugin->is_updating): ?>
            <div class="inline-flex items-center gap-x-2 bg-orange-700 px-4 py-2 font-bold"><?= icon(
                'loop-left-fill',
                [
                    'class' => 'animate-spin',
                ],
            ) ?><?= lang('Plugin.updating') ?></div>
        <?php else: ?>
            <form action="<?= route_to('plugin-action', $plugin->key) ?>" method="POST">
                <?= csrf_field() ?>
                <?= altcha_widget([
                    'floating',
                    'style' => '--altcha-color-base: #003c0a;--altcha-color-border:#009486;--altcha-border-width:2px;',
                ]) ?>
                <button type="submit"  class="inline-flex justify-center items-center gap-x-2 bg-brand-50/20 px-4 py-2 ring-2 ring-brand-50 focus:ring-orange-700 ring-inset font-bold cursor-pointer shrink-0" name="action" value="update"><?= icon(
                    'loop-left-fill',
                ) ?><?= lang('Plugin.update') ?></button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
    <form action="<?= route_to(
        'plugin-version-download',
        $plugin->key,
        $plugin->selected_version->tag,
    ) ?>" method="POST">
        <?= csrf_field() ?>
        <?= altcha_widget([
            'floating',
            'style' => '--altcha-color-base: #003c0a;--altcha-color-border:#009486;--altcha-border-width:2px;',
        ]) ?>
        <button type="submit" class="inline-flex items-center gap-x-2 bg-brand-50 px-4 py-2 font-bold text-brand-950"><?= lang(
            'Plugin.download',
        ) ?> (.zip)<?= icon('download-fill') ?></button>
    </form>
</div>
<?php $this->endSection(); ?>

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
<hr class="my-8 border-subtle border-t-2 container">
<div class="pb-6 container">
    <h2 class="font-display text-2xl">⚠️ <?= lang('Plugin.dangerZone') ?></h2>
    <form action="<?= route_to('plugin-action', $plugin->key) ?>" method="POST">
        <?= csrf_field() ?>
        <?= altcha_widget(['floating']) ?>
        <button class="mt-4 px-4 py-2 btn-danger" name="action" value="delete" type="submit"><?= icon(
            'delete-bin-fill',
            [
                'class' => 'text-on-error/75',
            ],
        ) ?><?= lang('Plugin.delete') ?></button>
    </form>
</div>
<?php endif; ?>
<?php $this->endSection() ?>