<?php

use App\Entities\Enums\Category;
use App\Entities\Plugin;
use CodeIgniter\Pager\Pager;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var string $q */
/** @var list<string> $selectedCategories */
/** @var Plugin[] $plugins */
/** @var Pager $pager */
?>

<?php $this->extend('_layout') ?>

<?php $this->section(
    'headerLeft',
) ?><h1 class="font-display font-bold text-5xl"><?= lang('Search.title') ?></h1><?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="flex flex-col container">
        <form class="z-10 flex items-center bg-surface-bright -mt-8 ring-2 ring-contrast has-[input[type='search']:focus]:ring-4 w-full transition" action="<?= route_to(
            'search',
        ) ?>" method="GET" hx-boost="true" hx-target="#plugin-list">
            <button
                type="button"
                class="inline-flex items-center gap-x-1 px-4 lg:px-8 py-4 h-full text-sm"
                id="categories-dropdown"
                data-dropdown="button"
                data-dropdown-target="categories-dropdown-menu"
                aria-haspopup="true"
                aria-expanded="false"><?= // @phpstan-ignore-next-line binaryOp.invalid
                icon('filter-2-fill', [
                    'class' => 'md:hidden text-xl text-skin-muted',
                ]) . '<span class="hidden md:inline">' . lang('Search.categories.title') . '</span>' . icon('arrow-drop-down-fill', [
                    'class' => 'text-2xl text-skin-muted',
                ]) ?></button>
            <div id="categories-dropdown-menu"
                    class="flex flex-col bg-surface-bright px-6 pb-4 border-2 whitespace-nowrap"
                    aria-labelledby="categories-dropdown" data-dropdown="menu" data-dropdown-placement="bottom-start">
                <?php foreach (Category::values() as $category): ?>
                    <label class="inline-flex items-center gap-x-2 py-2">
                        <input type="checkbox" name="categories[]" value="<?= $category ?>" class="checked:bg-primary" <?= in_array(
                            $category,
                            $selectedCategories,
                            true,
                        ) ? 'checked="checked"' : '' ?>>
                    <?= lang(sprintf('Search.categories.options.%s', $category)) ?>
                </label>
                <?php endforeach; ?>
                <button type="submit" class="mt-2 px-4 py-2 btn-primary"><?= lang('Search.applyFilters') ?></button>
            </div>
            <div class="flex items-center pl-2 border-contrast border-l-2 w-full">
                <button class="place-items-center grid h-10 aspect-square" title="<?= lang('Search.submit') ?>">
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
            <?php if ($selectedCategories !== []): ?>
            <div class="flex items-center gap-x-2 mt-4">
                <ul class="flex flex-wrap gap-2"><?php foreach ($selectedCategories as $category): ?>
                <li class="relative bg-brand-200 py-1 pr-8 pl-2 font-bold text-brand-950 text-sm"><?= lang(sprintf('Search.categories.options.%s', $category)) ?><a class="top-0 right-0 absolute p-1 h-full aspect-square text-skin-muted hover:text-brand-950 text-xl" href="<?= remove_category_from_current_url($category) ?>" title="<?= lang('Search.removeCategoryFilter', [
                    'category' => $category,
                ]) ?>"><?= icon('close-fill') ?></a></li>
                <?php endforeach; ?></ul>
            </div>
            <?php endif; ?>
            <?php if ($plugins === []): ?>
               <p class="mt-8 text-4xl"><?= $q === '' ? lang('Search.noResult') : lang('Search.noResultWithQuery', [
                   'query' => '<strong>' . $q . '</strong>',
               ]) ?></p>
            <?php else: ?>
            <div class="flex flex-col">
                <div class="items-start gap-8 grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] py-8">
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
                <?= $pager->links() ?>
            </div>
            <?php endif; ?>
            <?php $this->endFragment() ?>
        </section>
    </div>
<?php $this->endSection() ?>
