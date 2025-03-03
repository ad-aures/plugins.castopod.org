<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PluginModel;

class Home extends BaseController
{
    public function index(): string
    {
        $plugins = new PluginModel()
            ->findAll();

        return view('home', [
            'plugins' => $plugins,
        ]);
    }
}
