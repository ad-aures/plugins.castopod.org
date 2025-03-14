<?php

declare(strict_types=1);

namespace App\Models;

use App\Entities\Index;

class IndexModel extends BaseModel
{
    protected $table = 'index';

    protected $returnType = Index::class;

    protected $allowedFields = ['repository_url', 'repository_folder'];

    protected $createdField = 'submitted_at';

    protected $updatedField = '';

    protected $useTimestamps = true;
}
