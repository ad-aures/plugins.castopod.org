<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PluginModel;
use CodeIgniter\API\ResponseTrait;

class Plugins extends BaseController
{
    use ResponseTrait;

    public function index(): string
    {
        $q = $this->request->getGet('q');
        $categories = $this->request->getGet('categories');

        $db = db_connect();
        $pluginModel = new PluginModel();

        if (! in_array($q, ['', null], true)) {
            /** @var string $escapedQ */
            $escapedQ = $db->escape($q);
            $pluginModel->where("text_searchable @@ to_tsquery({$escapedQ})");
        }

        if ($categories !== null) {
            /** @var list<string> $escapedCategories */
            $escapedCategories = $db->escape($categories);
            $categoriesString = implode(',', $escapedCategories);
            $pluginModel->where("categories && array[{$categoriesString}]::plugin_category[]", null, false);
        }

        $plugins = $pluginModel->paginate(12);
        $pager = $pluginModel->pager;

        if ($this->request->isHtmx()) {
            return view_fragment('index', 'plugins', [
                'q'          => $q ?? '',
                'categories' => $categories ?? [],
                'plugins'    => $plugins,
                'pager'      => $pager,
            ]);
        }

        return view('index', [
            'q'          => $q ?? '',
            'categories' => $categories ?? [],
            'plugins'    => $plugins,
            'pager'      => $pager,
        ]);
    }
}
