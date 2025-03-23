<?php
use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('info/_layout') ?>

<?php $this->section('content') ?>
<div class="prose-invert max-w-prose prose">
    <?php if ($plugin->selected_version->readme_markdown): ?>
        <?= $plugin->selected_version->readme_markdown->renderHTML() ?>
    <?php else: ?>
        TODO: NO README!
    <?php endif; ?>
</div>
<?php $this->endSection() ?>