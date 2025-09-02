<?php
use App\Entities\Plugin;

/** @var int $key */
/** @var Plugin $plugin */
?>

<article class="isolate relative flex flex-col bg-surface-bright starting:opacity-0 p-4 ring-2 ring-contrast focus-within:ring-4 h-full transition starting:translate-y-full hover:-translate-y-0.5 active:translate-y-1 starting:duration-500 starting:ease-in-out">
    <header>
        <?php if ($plugin->icon_svg): ?>
            <?= $plugin->icon_svg ?>
        <?php else: ?>
            <div class="place-items-center grid bg-brand-700 rounded-full w-16 h-16 text-brand-200"><?= icon(
                'puzzle-2-fill',
                [
                    'class' => 'text-4xl',
                ],
            ) ?></div>
        <?php endif; ?>
        <h2 class="mt-2"><a href="<?= route_to(
            'plugin-info',
            $plugin->key,
        ) ?>" class="focus:outline-0"><span class="z-10 absolute inset-0"></span><span class="text-sm"><?= $plugin->vendor ?>/</span><span class="-mt-1 font-bold line-clamp-1"><?= $plugin->name ?></span></a></h2>
    </header>
    <p class="mt-2 text-sm line-clamp-3"><?= $plugin->description ?></p>
    <footer class="flex justify-between items-center mt-auto pt-4">
        <small><?= number_abbr($plugin->downloads_total) ?> downloads</small>
        <?= icon('arrow-right-long-line', [
            'class' => 'text-2xl',
        ]) ?>
    </footer>
</article>
