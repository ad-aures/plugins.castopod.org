<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Download;
use CodeIgniter\Model;

class DownloadModel extends Model
{
    protected $table = 'downloads';

    protected $returnType = Download::class;

    protected $allowedFields = ['version_id', 'date', 'count'];
}
