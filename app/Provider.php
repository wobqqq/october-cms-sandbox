<?php

declare(strict_types=1);

namespace App;

use App\Console\CreateAdmin;
use App\Console\RollbackPlugin;
use Backend\Facades\Backend;
use System\Classes\AppBase;

class Provider extends AppBase
{
    public function register(): void
    {
        parent::register();

        $this->registerConsoleCommand('sandbox.admin', CreateAdmin::class);
        $this->registerConsoleCommand('sandbox.rollback', RollbackPlugin::class);
    }

    /**
     * @return array<string, array<string, callable>>
     */
    public function registerMarkupTags(): array
    {
        return [
            'functions' => [
                'backend_url' => static fn (string $path = ''): string => Backend::url($path),
            ],
        ];
    }
}
