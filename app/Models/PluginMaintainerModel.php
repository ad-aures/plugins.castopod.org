<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\PluginMaintainer;
use CodeIgniter\Database\BaseResult;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Model;

class PluginMaintainerModel extends Model
{
    protected $table = 'plugins_maintainers';

    protected $useAutoIncrement = false;

    protected $returnType = PluginMaintainer::class;

    protected $allowedFields = ['plugin_key', 'user_id'];

    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $updatedField = '';

    protected $createdField = 'added_at';

    public function addMaintainer(string $pluginKey, int $userId): BaseResult|bool
    {
        /** @var BaseResult|bool */
        return $this->builder()
            ->set([
                'plugin_key' => $pluginKey,
                'user_id'    => $userId,
                'added_at'   => new RawSql('NOW()'),
            ])->insert();
    }

    public function removeMaintainer(string $pluginKey, int $userId): BaseResult|bool
    {
        return $this->where([
            'plugin_key' => $pluginKey,
            'user_id'    => $userId,
        ])->delete();
    }
}
