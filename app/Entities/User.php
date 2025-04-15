<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

/**
 * @property string $avatar_path
 */
class User extends ShieldUser
{
    /**
     * @param 'tiny' $size
     */
    public function getAvatarUrl(?string $size = null): string
    {
        $path = $this->avatar_path;

        if ($size !== null) {
            $path = preg_replace('"\.(jpg)$"', sprintf('-%s.%s', $size, 'jpg'), $path);
        }

        return '/' . trim((string) config('App')->avatarsFolder, '/') . $path;
    }
}
