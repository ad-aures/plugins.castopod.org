<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Version;
use CodeIgniter\Model;

class VersionModel extends Model
{
    protected $table = 'versions';

    protected $returnType = Version::class;

    protected $allowedFields = ['plugin_id', 'tag', 'commit_hash', 'published_at'];
}
