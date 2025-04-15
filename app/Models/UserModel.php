<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\User;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $returnType = User::class;

    public function getPluginOwner(string $pluginKey): User
    {
        $cacheName = sprintf('plugin#%s_owner', str_replace('/', '_', $pluginKey));

        if (! ($found = cache($cacheName))) {
            $found = $this
                ->select('users.*')
                ->join('plugins', 'plugins.owner_id = users.id')
                ->where('plugins.key', $pluginKey)
                ->first();

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var User $found */
        return $found;
    }

    /**
     * @return User[]
     */
    public function getPluginMaintainers(string $pluginKey): array
    {
        $cacheName = sprintf('plugin#%s_maintainers', str_replace('/', '_', $pluginKey));

        if (! ($found = cache($cacheName))) {
            $found = $this
                ->select('users.*')
                ->join('plugins_maintainers', 'plugins_maintainers.user_id = users.id')
                ->where('plugins_maintainers.plugin_key', $pluginKey)
                ->findAll();

            cache()
                ->save($cacheName, $found, DECADE);
        }

        /** @var User[] $found */
        return $found;
    }

    #[\Override]
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [...$this->allowedFields, 'avatar_path'];
    }
}
