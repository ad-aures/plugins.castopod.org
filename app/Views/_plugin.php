<?php
use App\Entities\Plugin;

/** @var int $key */
/** @var Plugin $plugin */
?>

<a href="#" class="transition hover:-translate-y-0.5 active:translate-y-1">
    <article class="relative flex flex-col bg-white starting:opacity-0 p-4 transition starting:translate-y-full duration-500 ease-in-out delay-(--card-delay)" style="--card-delay:<?= 100 + ($key * 50) ?>ms">
        <header>
            <img alt="<?= $plugin->name ?> icon" src="" class="bg-brand-800 rounded-full w-16 h-16 overflow-hidden">
            <h2 class="font-bold line-clamp-2"><?= $plugin->name ?></h2>
        </header>
        <p class="text-sm line-clamp-3"><?= $plugin->description ?></p>
        <footer class="flex justify-between items-center mt-4">
            <small>2K downloads</small>
            <?= icon('arrow-right-long-line', [
                'class' => 'text-2xl',
            ]) ?>
        </footer>
    </article>
</a>
