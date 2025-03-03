<?php
use App\Entities\Plugin;

/** @var Plugin[] $plugins */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Castopod Plugin Repository</title>
    <meta name="description" content="The small framework with powerful features">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">
</head>
<body>
    <h1 class="text-3xl font-bold">Castopod Plugins</h1>
    <div class="grid grid-cols-4">
        <?php
        foreach ($plugins as $plugin):
            echo view('_plugin', [
                'plugin' => $plugin,
            ]);
        endforeach; ?>
    </div>
</body>
</html>
