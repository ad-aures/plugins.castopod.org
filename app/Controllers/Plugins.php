<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Index;
use App\Models\IndexModel;
use App\Models\PluginModel;
use CodeIgniter\Exceptions\PageNotFoundException;
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

        // order by newest addition by default
        $pluginModel->orderBy('created_at', 'DESC');

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

        $repositoryUrl = $validData['repository_url'];
        $manifestRoot = trim($validData['manifest_root'] ?? '', '/');
        if ($indexModel->doesPluginAlreadyExist($repositoryUrl, $manifestRoot)) {
            return $this->alert('error', 'Plugin has already been submitted.');
        }

        $idIndex = $indexModel
            ->insert(new Index([
                'repository_url' => $repositoryUrl,
                'manifest_root'  => $manifestRoot,
                'submitted_by'   => user_id(),
            ]));

        if (! $idIndex) {
            $db->transRollback();
            return $this->alert('error', $indexModel->errors(true));
        }

        if (! service('queue')->push('crawls', 'plugin-crawl', [
            'index_id' => $idIndex,
        ],)) {
            $db->transRollback();
            return $this->alert('error', 'Could not push crawl to queue.');
        }

        $db->transComplete();

        return $this->alert('success', 'Your plugin has been added to the index!');
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

    public function myPlugins(): string
    {
        $userId = user_id();
        if (! is_int($userId)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $plugins = new PluginModel()
            ->getUserPlugins($userId);

        return view('my-plugins', [
            'plugins' => $plugins,
        ]);
    }

    public function action(string $pluginKey): RedirectResponse|string
    {
        $rules = [
            'action' => 'required|string|in_list[update,delete,edit]',
        ];

        /** @var array<string,string> $data */
        $data = $this->request->getPost(array_keys($rules));

        $isValid = $this->validateData($data, $rules);

        assert($this->validator instanceof Validation);

        if (! $isValid) {
            return $this->alert('error', $this->validator->getErrors());
        }

        /** @var array{action:'update'|'delete'|'edit'} */
        $validData = $this->validator->getValidated();

        return match ($validData['action']) {
            'update' => $this->update($pluginKey),
            'delete' => $this->delete($pluginKey),
            'edit'   => $this->editAction($pluginKey),
        };
    }

    public function edit(string $pluginKey): string
    {
        $plugin = new PluginModel()
            ->getPluginByKey($pluginKey);

        return view('info/edit', [
            'plugin' => $plugin,
        ]);
    }

    private function editAction(string $pluginKey): RedirectResponse|string
    {
        $plugin = new PluginModel()
            ->getPluginByKey($pluginKey);

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

        $indexModel = new IndexModel();
        $index = $indexModel->where(
            [
                'repository_url' => $plugin->repository_url,
                'manifest_root'  => $plugin->manifest_root,
            ],
        )->first();

        if (! $index instanceof Index) {
            return $this->alert('error', 'Could not find plugin in index.', true);
        }

        $repositoryUrl = $validData['repository_url'];
        $manifestRoot = trim($validData['manifest_root'] ?? '', '/');

        $index->repository_url = $repositoryUrl;
        $index->manifest_root = $manifestRoot;

        if (! $index->hasChanged()) {
            return $this->alert('info', 'Nothing changed.');
        }

        if ($indexModel->doesPluginAlreadyExist($repositoryUrl, $manifestRoot)) {
            return $this->alert('error', 'Plugin collides with another one.');
        }

        if (! $indexModel->save($index)) {
            return $this->alert('error', $indexModel->errors(true));
        }

        new PluginModel()
            ->clearCache([
                'id' => $plugin->id,
            ]);

        return $this->alert('success', 'Your plugin repository settings have been updated!');
    }

    private function update(string $pluginKey): RedirectResponse|string
    {
        $plugin = new PluginModel()
            ->getPluginByKey($pluginKey);

        if ($plugin->is_updating) {
            return $this->alert('error', 'The plugin is already updating.');
        }

        $db = db_connect();
        $db->transStart();

        $isUpdating = new PluginModel()
            ->setUpdating($plugin->id, true);

        if (! $isUpdating) {
            $db->transRollback();
            return $this->alert('error', 'Could not flag plugin as updating.');
        }

        if (! service('queue')->push('updates', 'plugin-update', [
            'plugin_key' => $pluginKey,
        ])) {
            $db->transRollback();
            return $this->alert('error', 'Could not push the update to the queue.');
        }

        $db->transComplete();

        return $this->alert('success', 'Your plugin has been added to the update queue!');
    }

    private function delete(string $pluginKey): RedirectResponse|string
    {
        $plugin = new PluginModel()
            ->getPluginByKey($pluginKey);

        if (! new IndexModel()->deletePluginFromIndex($plugin)) {
            return $this->alert('error', 'Could not delete plugin ' . $pluginKey);
        }

        return $this->alert('success', 'Your plugin has been deleted!');
    }
}
