<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Install;

class DownloadModel extends BaseModel
{
    protected $table = 'installs';

    protected $returnType = Install::class;

    protected $allowedFields = ['version_id', 'date', 'count'];
}
