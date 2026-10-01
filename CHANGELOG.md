# Changelog

All notable changes are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- An empty October CMS 4.4 project on Laravel 12 and PHP 8.4 in Docker: nginx, MySQL 8.4, Redis, Mailpit.
- The `sandbox` theme with the home, 404, error and maintenance pages.
- `sandbox:admin` creates the local administrator; `sandbox:rollback` rolls a plugin back without touching its files.
- `bin/plugin` and the `plugin.*` make targets: link a local plugin checkout or require a plugin with composer.
- Pest, PHPStan at level max with Larastan, php-cs-fixer, Rector, yaml-lint and composer normalize.
