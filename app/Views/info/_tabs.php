<?php
/** @var string $currentTab */

use CodeIgniter\HTTP\URI;

$tabs = ['readme', 'versions'];

?>

<?php foreach ($tabs as $tab):
    /** @var URI $currentUrl */
    $currentUrl = current_url(true);
    $href = $currentUrl->addQuery('tab', $tab);
    $isActive = $tab === $currentTab;
    ?>
<a href="<?= $href ?>" class="px-4 py-2 font-bold  <?= $isActive ? "relative bg-surface-bright after:content-[''] after:absolute after:-bottom-0.5 after:left-0 after:bg-surface-bright after:z-20 after:w-full after:h-0.5" : 'bg-surface-base hover:bg-primary hover:text-on-primary' ?>"><?= lang(
    'Plugin.tabs.' . $tab,
) ?></a>
<?php endforeach; ?>