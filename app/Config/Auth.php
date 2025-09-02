<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

class Auth extends ShieldAuth
{
    public string $userProvider = \App\Models\UserModel::class;

    /**
     * --------------------------------------------------------------------
     * View files
     * --------------------------------------------------------------------
     */
    public array $views = [
        'login'                       => '\App\Views\auth\login',
        'register'                    => '\App\Views\auth\register',
        'layout'                      => '\App\Views\auth\_layout',
        'action_email_2fa'            => '\App\Views\auth\email_2fa_show',
        'action_email_2fa_verify'     => '\App\Views\auth\email_2fa_verify',
        'action_email_2fa_email'      => '\CodeIgniter\Shield\Views\Email\email_2fa_email',
        'action_email_activate_show'  => '\App\Views\auth\email_activate_show',
        'action_email_activate_email' => '\CodeIgniter\Shield\Views\Email\email_activate_email',
        'magic-link-login'            => '\App\Views\auth\magic_link_form',
        'magic-link-message'          => '\App\Views\auth\magic_link_message',
        'magic-link-email'            => '\CodeIgniter\Shield\Views\Email\magic_link_email',
    ];

    /**
     * @var array<string,string>
     */
    public array $redirects = [
        'register'          => '/',
        'login'             => '/',
        'logout'            => '/',
        'force_reset'       => '/',
        'permission_denied' => '/',
        'group_denied'      => '/',
    ];
}
