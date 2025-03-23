<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\PluginNotFoundException;
use App\Models\DownloadModel;
use App\Models\PluginModel;
use App\Models\VersionModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class API extends BaseController
{
    use ResponseTrait;

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

        $tags = [];
        foreach ($plugin->versions as $version) {
            $tags[] = $version->tag;
        }

        return $this->respond([
            'latest' => $plugin->latest_version->tag,
            'tags'   => $tags,
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

        return $this->respond([
            'name' => $pluginKey,
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
