<?php
use App\Entities\Plugin;

/** @var Plugin $plugin */
/** @var bool $canEdit */
?>
<div class="flex flex-col">
    <h2 class="font-bold text-skin-muted text-xs uppercase tracking-wider">Install</h2>
    <code class="flex items-center gap-x-1 bg-surface-bright mt-1 px-2 py-3 border-2 border-contrast border-dashed w-full overflow-x-scroll"><?= icon(
        'arrow-right-s-line',
        [
            'class' => 'text-xl shrink-0',
        ],
    ) ?><input type="text" readonly="readonly" class="bg-transparent p-0 border-0 ring-0 w-full font-mono text-sm" value="php spark plugins:add <?= $plugin->key ?><?= $plugin->selected_version->tag !== $plugin->latest_version->tag ? '@' . $plugin->selected_version->tag : '' ?>"></code>
    <p class="mt-1 text-skin-muted text-sm">Compatible with <strong>Castopod v<?= $plugin->selected_version->min_castopod_version ?> and up</strong></p>
</div>

<div class="flex flex-col items-start mt-6">
    <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Repository</dt>
    <dd class="truncate"><a href="<?= $plugin->repository_url ?>" class="font-bold decoration-2 decoration-primary underline hover:no-underline"><?= $plugin->repository_url ?></a></dd>
    <?php if ($canEdit): ?>
    <a class="mt-2 btn-secondary" href="<?= route_to(
        'plugin-edit',
        $plugin->key,
    ) . '?tab=repository' ?>" class="px-y"><?= icon(
        'edit-fill',
    ) ?>Edit repository</a>
    <?php endif; ?>
</div>

<?php if ($plugin->homepage_url): ?>
<div class="flex flex-col mt-6">
    <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Homepage</dt>
    <dd class="truncate"><a href="<?= $plugin->homepage_url ?>" class="font-bold decoration-2 decoration-primary underline hover:no-underline"><?= $plugin->homepage_url ?></a></dd>
</div>
<?php endif; ?>

<hr class="my-6 border-subtle border-t-2">

<h2 class="sr-only">Metadata</h2>

<dl class="gap-y-6 grid grid-cols-2">
    <div>
        <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Version</dt>
        <dd class="font-mono font-bold text-lg"><?= $plugin->selected_version->tag ?></dd>
    </div>
    <div>
        <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">License</dt>
        <dd class="font-bold text-lg"><?= $plugin->selected_version->license->value ?></dd>
    </div>
    <div class="col-span-2">
        <dt class="inline-flex items-center gap-x-1 font-bold text-skin-muted text-xs uppercase tracking-wider"><?= icon(
            'mdi:hook',
            [
                'class' => 'text-sm',
            ],
        ) ?>Hooks</dt>
        <dd class="flex flex-wrap gap-2">
        <?php foreach ($plugin->selected_version->hooks as $hook): ?>
            <span class="bg-surface-dim px-2 font-bold text-sm"><?= $hook->value ?></span>
        <?php endforeach; ?>
        </dd>
    </div>
    <?php if (config('App')->displayDownloads): ?>
    <div class="col-span-2">
        <dt class="inline-flex items-center gap-x-1 font-bold text-skin-muted text-xs uppercase tracking-wider"><?= icon(
            'install-fill',
            [
                'class' => 'text-sm',
            ],
        ) ?>Total downloads</dt>
        <dd class="flex items-center gap-x-2 font-bold text-lg"><?= number_format(
            $plugin->downloads_total,
        ) ?></dd>
    </div>
    <?php endif; ?>
    <div class="col-span-2">
        <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Last publication</dt>
        <dd class="font-bold text-lg"><?= relative_time($plugin->selected_version->published_at) ?></dd>
    </div>
    <div>
        <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Size</dt>
        <dd class="font-bold text-lg"><?= format_bytes($plugin->selected_version->size, true) ?></dd>
    </div>
    <div>
        <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Total files</dt>
        <dd class="font-bold text-lg"><?= $plugin->selected_version->file_count ?></dd>
    </div>
    <div class="col-span-2">
        <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Authors</dt>
        <dd class="font-bold">
            <ul class="flex flex-col gap-y-2 pt-2">
                <?php foreach ($plugin->authors as $author): ?>
                    <li>
                        <?php if (! $author->url): ?>
                            <?= $author->name ?>
                        <?php else: ?>
                            <a class="inline-flex items-center decoration-2 decoration-primary underline hover:no-underline" href="<?= $author->url ?>" target="_blank" rel="noopener noreferrer"><?= $author->name . icon(
                                'external-link-line',
                                [
                                    'class' => 'ml-1',
                                ],
                            ) ?></a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </dd>
    </div>
    <div class="col-span-2">
        <dt class="font-bold text-skin-muted text-xs uppercase tracking-wider">Maintainers</dt>
        <dd>
            <ul class="flex gap-2 pt-2">
                <li><img class="h-12" src="<?= $plugin->owner->avatar_url ?>" alt="<?= $plugin->owner->username ?>" title="<?= $plugin->owner->username ?>" data-tooltip="bottom"></li>    
                <?php foreach ($plugin->maintainers as $maintainer): ?>
                    <li><img class="h-12" src="<?= $maintainer->avatar_url ?>" alt="<?= $maintainer->username ?>" title="<?= $maintainer->username ?>" data-tooltip="bottom"></li>    
                <?php endforeach; ?>
            </ul>
            <?php if ($canEdit): ?>
            <a class="mt-2 btn-secondary" href="<?= route_to(
                'plugin-edit',
                $plugin->key,
            ) . '?tab=maintainers' ?>" class="px-y"><?= icon(
                'edit-fill',
            ) ?>Edit maintainers</a>
            <?php endif; ?>
        </dd>
    </div>
</dl>
