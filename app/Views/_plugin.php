<?php
use App\Entities\Plugin;

/** @var Plugin $plugin */
?>

<article class="flex flex-col p-4 rounded-xl relative">
    <img alt="<?= $plugin->name ?> icon" src="">
    <h2 class="font-bold"><?= $plugin->name ?></h2>
    <p class="text-sm line-clamp-3"><?= $plugin->description ?></p>
</article>
