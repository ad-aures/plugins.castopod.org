<?php

use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('info/_layout_edit') ?>

<?php $this->section('content') ?>
    <h2 class="font-display text-2xl"><?= lang('Plugin.editMaintainersForm.addMaintainer') ?></h2>
    <form method="POST" action="<?= route_to(
        'plugin-action',
        $plugin->key,
    ) ?>" class="flex flex-col gap-2 mt-2 max-w-sm">
        <div class="flex flex-col">
            <label for="maintainer_username_or_email" class="font-semibold"><?= lang(
                'Plugin.editMaintainersForm.usernameOrEmail',
            ) ?></label>
            <input type="text" id="maintainer_username_or_email" name="maintainer_username_or_email" autofocus class="border-0 ring-2 ring-contrast focus:ring-4 w-full transition">
        </div>

        <?= altcha_widget(['floating']) ?>
        <button class="self-end px-4 py-2 btn-primary" name="action" value="add-maintainer" type="submit"><?= lang(
            'Common.forms.add',
        ) ?></button>
    </form>

    <div class="mt-8">
        <h2 class="font-display text-2xl"><?= lang('Plugin.editMaintainersForm.list') ?></h2>
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

                            <?= altcha_widget(['floating']) ?>
                            <button class="self-start px-1 btn-danger" name="action" value="remove-maintainer" type="submit"><?= lang(
                                'Common.forms.remove',
                            ) ?></button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php $this->endSection() ?>
