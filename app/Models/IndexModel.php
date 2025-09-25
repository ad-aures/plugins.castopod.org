<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Index;
use App\Entities\Plugin;
use CodeIgniter\Database\BaseResult;

class IndexModel extends BaseModel
{
    protected $table = 'index';

    protected $returnType = Index::class;

    protected $allowedFields = ['repository_url', 'manifest_root', 'submitted_by'];

    protected $createdField = 'submitted_at';

    protected $updatedField = '';

    protected $useTimestamps = true;

    public function getIndexRecord(string $repositoryUrl, string $manifestRoot): ?Index
    {
        // FIXME: collision issue
        $cacheName = sprintf('index#%s', hash('md5', $repositoryUrl . $manifestRoot));

        if (! ($found = cache($cacheName))) {
            $found = $this->where([
                'repository_url' => $repositoryUrl,
                'manifest_root'  => $manifestRoot,
            ])->first();

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var ?Index $found */
        return $found;
    }

    public function deletePluginFromIndex(Plugin $plugin): BaseResult|bool
    {
        // clear all plugin cache before removing it from index
        new PluginModel()
            ->clearCache([
                'id' => $plugin->id,
            ]);

        return $this
            ->where([
                'repository_url' => $plugin->repository_url,
                'manifest_root'  => $plugin->manifest_root,
            ])->delete();
    }

    public function doesPluginAlreadyExist(string $repositoryUrl, string $manifestRoot): bool
    {
        return (bool) $this->where([
            'repository_url' => $repositoryUrl,
            'manifest_root'  => $manifestRoot,
        ])->countAllResults();
    }
}
