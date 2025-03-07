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

        $pluginModel = new PluginModel();

        $query = $pluginModel;
        if (! in_array($q, ['', null], true)) {
            /** @var string $escapedQ */
            $escapedQ = $pluginModel->db->escape($q);
            $query = $pluginModel
                ->where("text_searchable @@ to_tsquery({$escapedQ})");
        }

        if ($categories !== null) {
            /** @var list<string> $escapedCategories */
            $escapedCategories = $pluginModel->db->escape($categories);
            $categoriesString = implode(',', $escapedCategories);
            $query->where("categories && array[{$categoriesString}]::plugin_category[]", null, false);
        }

        $plugins = $query->findAll();

        if ($this->request->isHtmx()) {
            return view_fragment('index', 'plugins', [
                'plugins' => $plugins,
            ]);
        }

        return view('index', [
            'q'          => $q ?? '',
            'categories' => $categories ?? [],
            'plugins'    => $plugins,
        ]);
    }
}
