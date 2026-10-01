<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use System\Classes\PluginManager;

use function Pest\Laravel\get;

/*
 * Whatever is installed under plugins/ (linked from a sibling checkout or
 * required with composer) has to register, migrate and keep the site and the
 * backend answering.
 */

it('registers and migrates every installed plugin', function (): void {
    $manager = PluginManager::instance();

    /** @var array<string, System\Classes\PluginBase> $plugins */
    $plugins = $manager->getPlugins();

    expect($plugins)->toBeArray();

    foreach (array_keys($plugins) as $code) {
        expect($manager->isDisabled($code))->toBeFalse($code . ' is disabled')
            ->and(DB::table('system_plugin_versions')->where('code', $code)->exists())->toBeTrue($code . ' is not migrated');
    }
});

it('keeps the site up with the installed plugins', function (): void {
    get('/')->assertOk();
});

it('keeps the backend up with the installed plugins', function (): void {
    signInAsSuperuser();

    get(Backend\Facades\Backend::url('dashboard'))->assertOk();
    get(Backend\Facades\Backend::url('system/updates'))->assertOk();
});
