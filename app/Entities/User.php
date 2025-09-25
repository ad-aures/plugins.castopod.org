<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;
use RuntimeException;

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
            /** @var string|null $path */
            $path = preg_replace('"\.(jpg)$"', sprintf('-%s.%s', $size, 'jpg'), $path);

            if ($path === null) {
                throw new RuntimeException(sprintf('Something happened when getting avatar URL with size %s', $size));
            }
        }

        return media_url('avatars', $path);
    }
}
