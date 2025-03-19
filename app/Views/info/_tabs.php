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
<a href="<?= $href ?>" class="px-4 py-2 text-brand-100 font-bold  <?= $isActive ? 'bg-brand-950' : 'bg-brand-900 hover:bg-brand-800' ?>"><?= $tab ?></a>
<?php endforeach; ?>