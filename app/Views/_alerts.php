<?php /** @var array<int,array{0:string,1:string}> $alerts */ ?>

<?php foreach ($alerts as $key => $alert): ?>
<?php
    [$class, $content] = $alert;
    $twClass = match ($class) {
        'info'    => 'bg-info',
        'warning' => 'bg-warning',
        'error','danger' => 'bg-error',
        'success' => 'bg-success',
        default   => '',
    };
    ?>
	<dialog x-data="{ close() { $refs.alertDialogRef.remove(); } }" x-ref="alertDialogRef" class="relative starting:opacity-0 starting:-translate-y-full px-4 py-2 flex items-center gap-2 transition duration-500 delay-(--alert-delay) <?= $twClass ?>" open style="--alert-delay:<?= 100 + ($key * 50) ?>ms">
		<?= $content ?>
		<button type="button" @click="close()" class="-mr-2 p-2 text-2xl"><?= icon('close-fill') ?></button>
	</dialog>
<?php endforeach; ?>
