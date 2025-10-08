<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Index;
use App\Entities\Plugin;
use App\Entities\User;
use App\Models\DownloadModel;
use App\Models\IndexModel;
use App\Models\PluginMaintainerModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
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
            $pluginModel->where("text_searchable @@ websearch_to_tsquery({$escapedQ})");
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
                'q'                  => $q ?? '',
                'selectedCategories' => $categories ?? [],
                'plugins'            => $plugins,
                'pager'              => $pager,
            ]);
        }

        return view('index', [
            'q'                  => $q ?? '',
            'selectedCategories' => $categories ?? [],
            'plugins'            => $plugins,
            'pager'              => $pager,
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
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.alreadySubmitted'));
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

            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.pushCrawlError.'));
        }

        $db->transComplete();

        // @phpstan-ignore argument.type
        return $this->alert('success', lang('Plugin.success.addedToIndex'));
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

        $permissions = [
            'canUpdate' => false,
            'canDelete' => false,
            'canEdit'   => false,
        ];

        if (user_id() === $plugin->owner_id) {
            $permissions['canUpdate'] = true;
            $permissions['canEdit'] = true;
            $permissions['canDelete'] = true;
        } elseif (is_user_maintainer_of($key)) {
            $permissions['canUpdate'] = true;
        }

        return view('info/' . $currentTab, [
            'plugin'     => $plugin,
            'currentTab' => $currentTab,
            ...$permissions,
        ]);
    }

    public function download(string $key, string $versionTag): RedirectResponse|string
    {
        $version = new VersionModel()
            ->getPluginVersion($key, $versionTag);

        // increment download
        $result = new DownloadModel()
            ->incrementVersionDownloads($version->plugin_key, $version->tag);

        if (! $result) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.downloadError'));
        }

        if ($this->request->isHtmx()) {
            /** @phpstan-ignore method.notFound */
            return redirect()->hxLocation((string) $version->archive_url);
        }

        return redirect()->to((string) $version->archive_url);
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
            'action' => 'required|string|in_list[update,delete,edit-repository,add-maintainer,remove-maintainer]',
        ];

        /** @var array<string,string> $data */
        $data = $this->request->getPost(array_keys($rules));

        $isValid = $this->validateData($data, $rules);

        assert($this->validator instanceof Validation);

        if (! $isValid) {
            return $this->alert('error', $this->validator->getErrors());
        }

        /** @var array{action:'update'|'delete'|'edit-repository'|'add-maintainer'|'remove-maintainer'} */
        $validData = $this->validator->getValidated();

        $plugin = new PluginModel()
            ->getPluginByKey($pluginKey);

        return match ($validData['action']) {
            'update'            => $this->update($plugin),
            'delete'            => $this->delete($plugin),
            'edit-repository'   => $this->editRepository($plugin),
            'add-maintainer'    => $this->addMaintainer($plugin),
            'remove-maintainer' => $this->removeMaintainer($plugin),
        };
    }

    public function edit(string $pluginKey): string
    {
        $plugin = new PluginModel()
            ->getPluginByKey($pluginKey);

        /** @var string $tab */
        $tab = $this->request->getGet('tab') ?? 'repository';

        $currentTab = 'repository';
        if (in_array($tab, ['repository', 'maintainers'], true)) {
            $currentTab = $tab;
        }

        return view('info/edit_' . $tab, [
            'plugin'     => $plugin,
            'currentTab' => $currentTab,
        ]);
    }

    private function addMaintainer(Plugin $plugin): RedirectResponse|string
    {
        $rules = [
            'maintainer_username_or_email' => 'required',
        ];

        /** @var array<string,string> $data */
        $data = $this->request->getPost(array_keys($rules));

        $isValid = $this->validateData($data, $rules);

        assert($this->validator instanceof Validation);

        if (! $isValid) {
            return $this->alert('error', $this->validator->getErrors(), withInput: true);
        }

        $validData = $this->validator->getValidated();

        $users = auth()
            ->getProvider();
        if (str_contains((string) $validData['maintainer_username_or_email'], '@')) {
            $user = $users->findByCredentials([
                'email' => $validData['maintainer_username_or_email'],
            ]);
        } else {
            $user = $users->findByCredentials([
                'username' => $validData['maintainer_username_or_email'],
            ]);
        }

        if (! $user instanceof User) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.userNotFound', [
                'username' => $validData['maintainer_username_or_email'],
            ]));
        }

        new PluginMaintainerModel()
            ->addMaintainer($plugin->key, (int) $user->id);

        new PluginModel()
            ->clearCache([
                'id' => $plugin->id,
            ]);

        // @phpstan-ignore argument.type
        return $this->alert('success', lang('Plugin.success.maintainerAdded', [
            'username' => $user->username,
        ]));
    }

    private function removeMaintainer(Plugin $plugin): RedirectResponse|string
    {
        $rules = [
            'username' => 'required',
        ];

        /** @var array<string,string> $data */
        $data = $this->request->getPost(array_keys($rules));

        $isValid = $this->validateData($data, $rules);

        assert($this->validator instanceof Validation);

        if (! $isValid) {
            return $this->alert('error', $this->validator->getErrors(), withInput: true);
        }

        $validData = $this->validator->getValidated();

        $users = auth()
            ->getProvider();
        $user = $users->findByCredentials([
            'username' => $validData['username'],
        ]);

        if (! $user instanceof User) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.userNotFound', [
                'username' => $validData['username'],
            ]));
        }

        if (! new PluginMaintainerModel()->removeMaintainer($plugin->key, (int) $user->id)) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.removeMaintainerError', [
                'username' => $user->username,
            ]));
        }

        new PluginModel()
            ->clearCache([
                'id' => $plugin->id,
            ]);

        // @phpstan-ignore argument.type
        return $this->alert('success', lang('Plugin.success.maintainerRemoved', [
            'username' => $user->username,
        ]));
    }

    private function editRepository(Plugin $plugin): RedirectResponse|string
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

        $indexModel = new IndexModel();
        $index = $indexModel->where(
            [
                'repository_url' => $plugin->repository_url,
                'manifest_root'  => $plugin->manifest_root,
            ],
        )->first();

        if (! $index instanceof Index) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.notInIndex'), true);
        }

        $repositoryUrl = $validData['repository_url'];
        $manifestRoot = trim($validData['manifest_root'] ?? '', '/');

        $index->repository_url = $repositoryUrl;
        $index->manifest_root = $manifestRoot;

        if (! $index->hasChanged()) {
            // @phpstan-ignore argument.type
            return $this->alert('info', lang('Plugin.info.nothingChanged'));
        }

        if ($indexModel->doesPluginAlreadyExist($repositoryUrl, $manifestRoot)) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.conflict'));
        }

        if (! $indexModel->save($index)) {
            return $this->alert('error', $indexModel->errors(true));
        }

        new PluginModel()
            ->clearCache([
                'id' => $plugin->id,
            ]);

        // @phpstan-ignore argument.type
        return $this->alert('success', lang('Plugin.success.repositoryUpdated'));
    }

    private function update(Plugin $plugin): RedirectResponse|string
    {
        if ($plugin->is_updating) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.alreadyUpdating'));
        }

        $db = db_connect();
        $db->transStart();

        $isUpdating = new PluginModel()
            ->setUpdating($plugin->id, true);

        if (! $isUpdating) {
            $db->transRollback();
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugins.errors.updateFlagError'));
        }

        $pluginIndex = new IndexModel()
            ->getIndexRecord((string) $plugin->repository_url, $plugin->manifest_root);

        if (! $pluginIndex instanceof Index) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.notInIndex'));
        }

        if (! service('queue')->push('crawls', 'plugin-crawl', [
            'index_id' => $pluginIndex->id,
        ])) {
            $db->transRollback();
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.pushCrawlError'));
        }

        $db->transComplete();

        // @phpstan-ignore argument.type
        return $this->alert('success', lang('Plugin.success.addedToCrawl'));
    }

    private function delete(Plugin $plugin): RedirectResponse|string
    {
        if (user_id() !== $plugin->owner_id) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.deleteOwnerOnly'));
        }

        if (! new IndexModel()->deletePluginFromIndex($plugin)) {
            // @phpstan-ignore argument.type
            return $this->alert('error', lang('Plugin.errors.deleteError', [
                'pluginKey' => $plugin->key,
            ]));
        }

        // @phpstan-ignore argument.type
        return $this->alert('success', lang('Plugin.success.deleted', [
            'pluginKey' => $plugin->key,
        ]));
    }
}
