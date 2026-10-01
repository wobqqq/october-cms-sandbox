<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Command;
use System\Classes\PluginManager;
use System\Classes\UpdateManager;
use System\Facades\System;

final class RollbackPlugin extends Command
{
    /** @var string */
    protected $signature = 'sandbox:rollback {plugin : The plugin code (Wobqqq.Fortify) or its composer package (wobqqq/fortify-plugin)}';

    /** @var string */
    protected $description = 'Roll back the migrations of a plugin and leave its files alone';

    public function handle(): int
    {
        $manager = PluginManager::instance();
        $plugin = $this->argument('plugin');

        if (str_contains($plugin, '/')) {
            $plugin = System::composerToOctoberCode($plugin);
        }

        $code = $manager->normalizeIdentifier($plugin);

        if (!$manager->hasPlugin($code)) {
            $this->error(sprintf('The plugin "%s" is not installed.', $code));

            return self::FAILURE;
        }

        $updates = UpdateManager::instance();
        $updates->setNotesCommand($this);
        $updates->rollbackPlugin($code);

        $this->info(sprintf('Rolled back %s.', $code));

        return self::SUCCESS;
    }
}
