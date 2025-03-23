<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Index;
use App\Models\IndexModel;
use App\Models\PluginModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Validation\Validation;

class Plugins extends BaseController
{
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

    public function submit(): string
    {
        return view('submit');
    }

    public function submitAction(): RedirectResponse|string
    {
        $rules = [
            'repository_url' => 'required|valid_url_strict[https]',
            'manifest_root'  => 'permit_empty|regex_match[/^\/?[a-zA-Z0-9-_]+(\/[a-zA-Z0-9-_]+)*\/?$/]',
        ];

        /** @var array<string,string> $data */
        $data = $this->request->getPost(array_keys($rules));

        $isValid = $this->validateData($data, $rules);

        assert($this->validator instanceof Validation);

        if (! $isValid) {
            return $this->alert('error', $this->validator->getErrors(), withInput: true);
        }

        $validData = $this->validator->getValidated();

        $db = db_connect();
        $db->transStart();

        $indexModel = new IndexModel();
        $idIndex = $indexModel
            ->insert(new Index([
                'repository_url' => $validData['repository_url'],
                'manifest_root'  => trim($validData['manifest_root'] ?? '', '/'),
                'submitted_by'   => user_id(),
            ]));

        if (! $idIndex) {
            $db->transRollback();
            return $this->alert('error', $indexModel->errors());
        }

        service('queue')
            ->push('crawls', 'crawl-plugin', [
                'index_id' => $idIndex,
            ],);

        $db->transComplete();

        return $this->alert('success', 'Your plugin has been added!');
    }

    public function info(string $key, ?string $versionTag = null): string
    {
        $plugin = new PluginModel()
            ->getPluginByKey($key);

        $plugin->selected_version_tag = $versionTag;

        $tab = $this->request->getGet('tab') ?? 'readme';

        $currentTab = 'readme';
        if (in_array($tab, ['readme', 'versions'], true)) {
            $currentTab = $tab;
        }

        return view('info/' . $currentTab, [
            'plugin'     => $plugin,
            'currentTab' => $currentTab,
        ]);
    }
}
