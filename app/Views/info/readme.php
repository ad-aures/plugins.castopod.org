<?php
use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('info/_layout') ?>

<?php $this->section('content') ?>
<div class="max-w-prose prose">
    <?php if (! $plugin->is_official): ?>
        <div class="flex items-start gap-x-2 bg-amber-50 mb-4 px-4 py-3 ring-2 ring-amber-500"><?= // @phpstan-ignore-next-line binaryOp.invalid
        icon('alert-fill', [
            'class' => 'text-amber-600 shrink-0 mt-1 text-lg',
        ]) . lang('Plugin.securityWarning') ?></div>
    <?php endif; ?>
    <?php if ($plugin->selected_version->readme_markdown): ?>
        <?= $plugin->selected_version->readme_markdown->renderHTML() ?>
    <?php else: ?>
        <?= lang('Plugin.readmeNotFound') ?>
    <?php endif; ?>
</div>
<?php $this->endSection() ?>