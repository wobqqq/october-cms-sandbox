<?php

declare(strict_types=1);

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Pest\TestSuite;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->beforeEach(function (): void {
        resetRequestSingletons();

        // October registers only the plugins it finds installed.
        if (ensureSchemaMigrated()) {
            /** @var TestCase $test */
            $test = TestSuite::getInstance()->test;
            $test->refreshApplication();
        }

        beginTrackingWrites();
    })
    ->afterEach(function (): void {
        resetTestDatabase();
    })
    ->in('Feature');

pest()->extend(TestCase::class)
    ->beforeEach(function (): void {
        resetRequestSingletons();
    })
    ->in('Unit');

require_once __DIR__ . '/Helpers.php';

function ensureSchemaMigrated(): bool
{
    static $migrated = false;

    if ($migrated) {
        return false;
    }

    ob_start();

    try {
        Artisan::call('october:migrate');
    } finally {
        ob_end_clean();
    }

    rememberSeededRows();

    $migrated = true;

    return true;
}

/**
 * The rows the migrations create, so a reset returns a table to them.
 */
function rememberSeededRows(): void
{
    $seeded = [];

    /** @var list<string> $tables */
    $tables = Schema::getTableListing(DB::connection()->getDatabaseName(), false);

    foreach ($tables as $table) {
        $rows = DB::table($table)->get()->map(fn (object $row): array => (array) $row)->all();

        if ($rows !== []) {
            $seeded[$table] = $rows;
        }
    }

    $GLOBALS['__seededRows'] = $seeded;
}

function beginTrackingWrites(): void
{
    $GLOBALS['__writtenTables'] = [];

    DB::listen(function (QueryExecuted $query): void {
        $pattern = '/^\s*(?:insert(?:\s+ignore)?\s+into|replace\s+into|update|delete\s+from|'
            . 'truncate(?:\s+table)?)\s+`?([a-zA-Z0-9_]+)`?/i';

        if (preg_match($pattern, $query->sql, $matches) === 1) {
            /** @var array<string, bool> $written */
            $written = $GLOBALS['__writtenTables'] ?? [];
            $written[$matches[1]] = true;
            $GLOBALS['__writtenTables'] = $written;
        }
    });
}

function resetTestDatabase(): void
{
    /** @var array<string, bool> $written */
    $written = $GLOBALS['__writtenTables'] ?? [];

    if ($written === []) {
        return;
    }

    $connection = DB::connection();
    $connection->statement('SET FOREIGN_KEY_CHECKS=0');

    foreach (array_keys($written) as $name) {
        if (in_array($name, ['migrations', 'system_plugin_versions', 'system_plugin_history'], true)) {
            continue;
        }

        if (Schema::hasTable($name)) {
            $connection->table($name)->truncate();

            /** @var array<string, list<array<string, mixed>>> $seeded */
            $seeded = $GLOBALS['__seededRows'] ?? [];

            if (isset($seeded[$name])) {
                $connection->table($name)->insert($seeded[$name]);
            }
        }
    }

    $connection->statement('SET FOREIGN_KEY_CHECKS=1');
}
