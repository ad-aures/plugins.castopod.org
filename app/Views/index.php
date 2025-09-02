<?php

use App\Entities\Enums\Category;
use App\Entities\Plugin;
use CodeIgniter\Pager\Pager;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var string $q */
/** @var list<string> $categories */
/** @var Plugin[] $plugins */
/** @var Pager $pager */
?>

<?php $this->extend('_layout') ?>

<?php $this->section(
    'headerRight',
) ?><h1 class="font-display font-bold text-5xl">Castopod plugins</h1><?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="flex flex-col container">
        <form class="z-10 flex items-center bg-surface-bright -mt-8 ring-2 ring-contrast has-[input[type='search']:focus]:ring-4 w-full transition" action="<?= route_to(
            'search',
        ) ?>" method="GET" hx-boost="true" hx-target="#plugin-list">
            <button
                type="button"
                class="inline-flex items-center gap-x-2 px-8 py-4 h-full text-sm"
                id="categories-dropdown"
                data-dropdown="button"
                data-dropdown-target="categories-dropdown-menu"
                aria-haspopup="true"
                aria-expanded="false">Categories<?= icon('arrow-down-s-line', [
                    'class' => 'text-2xl',
            ]) ?></button>
            <div id="categories-dropdown-menu"
                    class="flex flex-col bg-surface-bright px-6 pb-4 border-2 whitespace-nowrap"
                    aria-labelledby="categories-dropdown" data-dropdown="menu" data-dropdown-placement="bottom-start">
                <?php foreach (Category::values() as $category): ?>
                    <label class="inline-flex items-center gap-x-2 py-2">
                        <input type="checkbox" name="categories[]" value="<?= $category ?>" class="checked:bg-primary" <?= in_array(
                            $category,
                            $categories,
                            true,
                            ) ? 'checked="checked"' : '' ?>>
                    <?= $category ?>
                </label>
                <?php endforeach; ?>
                <button type="submit" class="mt-2 px-4 py-2 btn-primary">Apply</button>
            </div>
            <div class="flex items-center pl-2 border-contrast border-l-2 w-full">
                <button class="place-items-center grid h-10 aspect-square">
                    <?= icon('search-line', [
                        'class' => 'text-2xl text-skin-muted',
                    ]); ?>
                </button>
                <input
                    type="search"
                    name="q"
                    placeholder="Search for a plugin"
                    class="bg-transparent p-2 border-0 ring-0 w-full"
                    hx-indicator=".htmx-indicator"
                    value="<?= $q ?>">
            </div>
        </form>
        <section id="plugin-list">
            <?php $this->fragment('plugins') ?>
            <div class="flex flex-col">
                <div class="items-start gap-8 grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] py-8">
                    <?php
                    foreach ($plugins as $key => $plugin):
                        echo view('_plugin', [
                            'key'    => $key,
                            'plugin' => $plugin,
                        ]);
                    endforeach; ?>
                </div>
                <?= $pager->links() ?>
            </div>
            <?php $this->endFragment() ?>
        </section>
    </div>
<?php $this->endSection() ?>
