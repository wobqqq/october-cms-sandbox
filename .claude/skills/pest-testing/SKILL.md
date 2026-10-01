---
name: pest-testing
description: >-
    Use when writing, running or changing tests in the sandbox: anything under
    tests/ (Pest files, tests/Pest.php, tests/Helpers.php, tests/TestCase.php,
    tests/bootstrap.php, tests/Fixtures), phpunit.xml, or when a test fails only
    in the full suite.
---

# Pest testing

Pest 4 against a real MySQL database, inside the php-fpm container:

```bash
make test            # composer test
make test.coverage   # pcov, fails below 90 % of app/
```

## How the suite runs

- `tests/bootstrap.php` creates `app_test` (never the working `app` database)
  and drops it when the run ends.
- `tests/Pest.php` migrates once per run with `october:migrate`, then rebuilds
  the application so October registers the plugins it just installed. After
  each test only the tables the test wrote to return to their migrated rows.
- `tests/TestCase.php` boots October like `modules/system/tests/TestCase.php`
  and flushes model listeners and `extend()` callbacks after each test.
- `resetRequestSingletons()` forgets what a real request would start without:
  `AuthManager`, user preferences, setting models, the CMS controller. Add any
  new singleton a test leaks to it.

## Writing a test

- Feature tests (`tests/Feature`) use the database; unit tests (`tests/Unit`)
  never do.
- Sign in with `signInAsSuperuser()`; create the user without signing in with
  `superuser()`.
- Run console commands through `command()`, which returns a typed
  `PendingCommand` for PHPStan.
- October's error handler is off under unit tests, so a test of an error page
  calls `System\Classes\ErrorHandler::handleCustomError()` directly.
- A test that needs a plugin copies `tests/Fixtures/plugin` into `plugins/`,
  forgets `PluginManager::MANIFEST_PLUGINS` and reloads the plugins; it removes
  the copy afterwards.
- `tests/Feature/PluginsTest.php` checks whatever is installed under
  `plugins/`: keep it passing with every plugin you link.
- PHPStan runs at level max on the tests too: no `$this` inside Pest
  closures (use the `Pest\Laravel` functions), assert types instead of
  casting `mixed`.
