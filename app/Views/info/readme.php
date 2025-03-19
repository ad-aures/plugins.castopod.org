<?php
use App\Entities\Plugin;
use App\Entities\Version;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
/** @var Version $currentVersion */
?>

<?php $this->extend('info/_layout') ?>

<?php $this->section('content') ?>
<div class="prose-invert max-w-prose prose">
    <?php if ($currentVersion->readme_markdown): ?>
        <?= $currentVersion->readme_markdown->renderHTML() ?>
    <?php else: ?>
        TODO: NO README!
    <?php endif; ?>
</div>
<?php $this->endSection() ?>