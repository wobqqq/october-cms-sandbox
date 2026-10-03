# Changelog

All notable changes are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.0.1] - 2026-10-03

### Fixed

- CI audits the dependencies without asking the licensed October gateway, which answered 403.

## [1.0.0] - 2026-10-01

### Added

- An empty October CMS 4.4 project on Laravel 12 and PHP 8.4 in Docker: nginx, MySQL 8.4, Redis, Mailpit.
- The `sandbox` theme with the home, 404, error and maintenance pages.
- `sandbox:admin` creates the local administrator; `sandbox:rollback` rolls a plugin back without touching its files.
- `bin/plugin` and the `plugin.*` make targets: link a local plugin checkout or require a plugin with composer.
- Pest, PHPStan at level max with Larastan, php-cs-fixer, Rector, yaml-lint and composer normalize.
- GitHub Actions without a license: composer validation and audit, code style, YAML, PHP syntax and workflow checks; a weekly audit and tagged releases.

[Unreleased]: https://github.com/wobqqq/october-cms-sandbox/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/wobqqq/october-cms-sandbox/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/wobqqq/october-cms-sandbox/releases/tag/v1.0.0
