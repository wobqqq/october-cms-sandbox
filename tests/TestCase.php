<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use October\Rain\Database\Model;
use October\Rain\Database\Pivot;
use October\Rain\Extension\Container as ExtensionContainer;
use ReflectionClass;
use System\Classes\PluginManager;

/**
 * Boots October as modules/system/tests/TestCase.php does, and registers and
 * boots every installed plugin once the schema that records them exists.
 */
abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        /** @var Application $app */
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $app['cache']->setDefaultDriver('array');
        $app->setLocale('en');

        $app['config']->set('logging.default', 'null');

        if (Schema::hasTable('system_plugin_versions')) {
            /** @var PluginManager $plugins */
            $plugins = $app->make('system.plugins');
            $plugins->registerAll(true);
            $plugins->bootAll(true);
        }

        return $app;
    }

    /**
     * Public for the hook in tests/Pest.php that rebuilds the application once
     * the migration has installed the plugins.
     */
    public function refreshApplication(): void
    {
        parent::refreshApplication();
    }

    /**
     * October keeps model listeners and Model::extend() callbacks in statics
     * that the plugins fill again on every boot.
     */
    protected function tearDown(): void
    {
        ExtensionContainer::clearExtensions();

        foreach (get_declared_classes() as $class) {
            if (!is_subclass_of($class, Model::class) || is_a($class, Pivot::class, true) || str_starts_with($class, 'Mockery_')) {
                continue;
            }

            if ((new ReflectionClass($class))->isInstantiable()) {
                $class::flushEventListeners();
            }
        }

        Model::flushEventListeners();

        parent::tearDown();
    }
}
