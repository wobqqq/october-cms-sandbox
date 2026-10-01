<?php

declare(strict_types=1);

namespace SandboxFixture\Demo;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /**
     * @return array<string, string>
     */
    public function pluginDetails(): array
    {
        return [
            'name' => 'Demo',
            'description' => 'A plugin the sandbox tests install and roll back.',
            'author' => 'Sandbox',
        ];
    }
}
