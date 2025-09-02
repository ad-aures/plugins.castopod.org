<?php
use App\Entities\Plugin;
use Michalsn\CodeIgniterHtmx\View\View;

/** @var View $this */
/** @var Plugin $plugin */
?>

<?php $this->extend('info/_layout') ?>

<?php $this->section('content') ?>
    <ol class="flex flex-col gap-y-4 versions-list">
        <?php foreach ($plugin->versions as $version): ?>
            <li>
                <a class="group flex justify-between" href="<?= route_to(
                    'plugin-version',
                    $plugin->key,
                    $version->tag,
                ) ?>">
                    <span class="tag">
                        <span class="font-mono font-bold decoration-2 decoration-bg-accent underline group-hover:no-underline"><?= $version->tag ?></span>
                        <?= $version->tag === $plugin->latest_version->tag ? ' (latest)' : '' ?>
                        <span class="leaders" aria-hidden="true"></span>
                    </span>
                    <span class="date"><?= relative_time($version->published_at) ?></span>
                    <span class="inline-flex justify-end items-center gap-x-1 ml-4 w-[6ch] font-mono"><?= number_abbr(
                        $version->downloads_total,
                    ) . icon(
                        'arrow-down-line',
                        [
                            'class' => 'text-skin-muted',
                        ],
                    ) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ol>
<?php $this->endSection() ?>