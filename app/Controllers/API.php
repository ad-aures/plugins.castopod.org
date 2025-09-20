<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\PluginNotFoundException;
use App\Models\DownloadModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;

/** @property IncomingRequest $request */
class API extends BaseController
{
    use ResponseTrait;

    public function health(): ResponseInterface
    {
        try {
            db_connect();
        } catch (DatabaseException) {
            return $this->failServerError();
        }

        return $this->respondNoContent();
    }

    public function pluginInfo(string $pluginKey): ResponseInterface
    {
        try {
            $plugin = new PluginModel()
                ->getPluginByKey($pluginKey);
        } catch (PluginNotFoundException $e) {
            return $this->failNotFound($e->getMessage());
        }

        return $this->respond($plugin->jsonSerialize());
    }

    public function pluginVersions(string $pluginKey): ResponseInterface
    {
        try {
            $plugin = new PluginModel()
                ->getPluginByKey($pluginKey);
        } catch (PluginNotFoundException $e) {
            return $this->failNotFound($e->getMessage());
        }

        $expand = $this->request->getGet('expand') ?? [];

        return $this->respond([
            'plugin' => is_array($expand) && in_array(
                'plugin',
                $expand,
                true,
            ) ? $plugin->jsonSerialize() : $pluginKey,
            'latest'   => $plugin->latest_version->tag,
            'all_tags' => $plugin->all_tags,
        ]);
    }

    public function versionInfo(string $pluginKey, ?string $versionTag = null): ResponseInterface
    {
        try {
            $version = $versionTag === null
            ? new VersionModel()
                ->getLatestPluginVersion($pluginKey)
            : new VersionModel()
                ->getPluginVersion($pluginKey, $versionTag);
        } catch (PluginNotFoundException $e) {
            return $this->failNotFound($e->getMessage());
        }

        $plugin = $pluginKey;
        $expand = $this->request->getGet('expand') ?? [];
        if (is_array($expand) && in_array('plugin', $expand, true)) {
            $plugin = new PluginModel()
                ->getPluginByKey($pluginKey)
                ->jsonSerialize();
        }

        return $this->respond([
            'plugin' => $plugin,
            ...$version->jsonSerialize(),
        ]);
    }

    public function incrementDownloads(string $pluginKey, string $versionTag): ResponseInterface
    {
        $result = new DownloadModel()
            ->incrementVersionDownloads($pluginKey, $versionTag);

        if (! $result) {
            return $this->fail(sprintf('Could not increment download count for %s@%s ', $pluginKey, $versionTag));
        }

        return $this->respondNoContent();
    }
}
