<?php

use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('info/_layout_edit') ?>

<?php $this->section('content') ?>
    <h2 class="font-display text-2xl">Add a maintainer</h2>
    <form method="POST" action="<?= route_to(
        'plugin-action',
        $plugin->key,
    ) ?>" class="flex flex-col gap-2 mt-2 max-w-sm" hx-swap="none" hx-boost="true">
        <div class="flex flex-col">
            <label for="maintainer_username_or_email" class="font-semibold">Username or Email</label>
            <input type="text" id="maintainer_username_or_email" name="maintainer_username_or_email" autofocus class="bg-brand-950 border-0 ring ring-brand-800 focus:ring-2 focus:ring-brand-500 w-full">
        </div>
        <button class="self-end px-4 py-2 btn-primary" name="action" value="add-maintainer" type="submit">Add</button>
    </form>

    <div class="mt-8">
        <h2 class="font-display text-2xl">List of maintainers</h2>
        <ul class="flex flex-col bg-surface-dim p-4 divide-border-contrast divide-y max-w-sm">
            <li class="flex items-center gap-x-2 py-2"><img class="h-12" src="<?= $plugin->owner->avatar_url ?>" alt="<?= $plugin->owner->username ?>"><span class="font-bold"><?= $plugin->owner->username ?></span></li>    
            <?php foreach ($plugin->maintainers as $maintainer): ?>
                <li class="flex items-center gap-x-2 py-2">
                    <img class="h-12" src="<?= $maintainer->avatar_url ?>" alt="<?= $maintainer->username ?>">
                    <div class="flex flex-col">
                        <span class="font-bold"><?= $maintainer->username ?></span>
                        <form method="POST" action="<?= route_to(
                            'plugin-action',
                            $plugin->key,
                        ) ?>">
                            <input type="hidden" name="username" value="<?= $maintainer->username ?>">
                            <button class="self-start px-1 btn-danger" name="action" value="remove-maintainer" type="submit">remove</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php $this->endSection() ?>
