<?php
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

<?php $this->section('title') ?>Castopod plugins<?php $this->endSection() ?>

<?php $this->section('main') ?>
    <div class="flex lg:flex-row flex-col items-start bg-brand-900 grow-1">
        <aside class="self-stretch lg:starting:opacity-0 px-4 py-8 lg:border-r border-brand-950 lg:w-80 text-brand-100 transition lg:starting:-translate-x-full duration-700 ease-out">
            <form action="<?= route_to(
                'search',
            ) ?>" method="GET" hx-boost="true" hx-target="#plugin-list" class="top-8 sticky">
                <div class="flex items-center bg-brand-950 ring ring-brand-800 focus-within:ring-brand-500 focus-within:ring-2 w-full">
                    <div class="place-items-center grid h-10 aspect-square">
                        <?= icon('search-line', [
                                        'class' => 'text-2xl text-brand-800',
                                    ]); ?>
                    </div>
                    <input
                        type="search"
                        name="q"
                        placeholder="Search for a plugin"
                        class="bg-transparent p-2 border-0 ring-0 w-full"
                        hx-indicator=".htmx-indicator"
                        value="<?= $q ?>">
                </div>
                <h2 class="mt-4 font-display text-2xl">Filter</h2>
                <details class="flex flex-col">
                    <summary class="font-semibold">Categories</summary>
                    <?php foreach ([
                                    'accessibility',
                                    'analytics',
                                    'monetization',
                                    'podcasting2',
                                    'privacy',
                                    'productivity',
                                    'seo',
                                ] as $category): ?>
                        <label>
                            <input type="checkbox" name="categories[]" value="<?= $category ?>" class="text-brand-500" <?= in_array(
                                $category,
                                $categories,
                                true,
                            ) ? 'checked="checked"' : '' ?>>
                            <?= $category ?>
                        </label>
                    <?php endforeach; ?>
                    <button type="submit" class="bg-brand-950 shadow mt-4 px-4 py-2 font-semibold hover:-translate-y-0.5 active:translate-y-1">Apply filters</button>
                </details>
            </form>
        </aside>
        <section id="plugin-list" class="grow">
            <?php $this->fragment('plugins') ?>
            <div class="flex flex-col">
                <div class="items-start gap-8 grid grid-cols-[repeat(auto-fill,minmax(20rem,1fr))] p-8 lg:border-0 border-t border-brand-950 grow">
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
