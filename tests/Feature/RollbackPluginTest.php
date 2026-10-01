<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use System\Classes\PluginManager;
use System\Facades\Manifest;

beforeEach(function (): void {
    File::copyDirectory(__DIR__ . '/../Fixtures/plugin', base_path('plugins/sandboxfixture/demo'));

    Manifest::forget(PluginManager::MANIFEST_PLUGINS);
    PluginManager::instance()->reloadPlugins();
    Artisan::call('october:migrate');
});

afterEach(function (): void {
    File::deleteDirectory(base_path('plugins/sandboxfixture'));

    Manifest::forget(PluginManager::MANIFEST_PLUGINS);
    PluginManager::instance()->reloadPlugins();
});

function demoIsMigrated(): bool
{
    return DB::table('system_plugin_versions')->where('code', 'SandboxFixture.Demo')->exists();
}

it('rolls back a plugin and keeps its files', function (string $plugin): void {
    expect(demoIsMigrated())->toBeTrue();

    command('sandbox:rollback', ['plugin' => $plugin])->assertSuccessful();

    expect(demoIsMigrated())->toBeFalse()
        ->and(base_path('plugins/sandboxfixture/demo/Plugin.php'))->toBeFile();
})->with([
    'plugin code' => 'SandboxFixture.Demo',
    'composer package' => 'sandboxfixture/demo-plugin',
]);

it('refuses a plugin that is not installed', function (): void {
    command('sandbox:rollback', ['plugin' => 'Nobody.Missing'])
        ->expectsOutputToContain('Nobody.Missing')
        ->assertFailed();
});
