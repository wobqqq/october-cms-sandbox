# AGENTS.md

Guidance for coding agents working in this repository.

## What this is

An empty October CMS 4.4 project (Laravel 12, PHP 8.4, Docker) for installing
and trying out plugins. It has no domain code: the application plugin
(`app/Provider.php`) only adds the `backend_url()` Twig function and the
`sandbox:admin` / `sandbox:rollback` commands, and the `sandbox` theme only has
the home and error pages. Keep it that way: what is tried here belongs to the
plugin being tried, in its own repository.

## The gate (run before every commit)

Everything runs in the php-fpm container; the host needs no PHP.

```bash
make code.fix        # composer normalize, php-cs-fixer, Rector
make code.check      # validate, normalize, audit, yaml-lint, cs, Rector, PHPStan max
make test            # Pest
make test.coverage   # fails below 90 % of app/
make ready           # all of the above
```

PHPStan runs at `level: max` with Larastan and the strict and deprecation
rules, without a baseline: fix the type, never add an ignore.

## Plugins

- `make plugin.link PLUGIN=../<checkout>` mounts a sibling checkout at
  `plugins/<vendor>/<name>` and migrates it; `make plugin.unlink` rolls it back
  and removes the mount. `bin/plugin` writes `docker-compose.override.yml` and
  `plugins/.links`; never edit them by hand.
- `make plugin.require PACKAGE=...` / `make plugin.remove PACKAGE=...` install
  through composer.
- Never run `php artisan plugin:remove` on a linked plugin: it deletes the files
  of the checkout. Use `sandbox:rollback`.
- Leave the committed state without plugins: `plugins/` holds only `.gitkeep`,
  and `composer.json` requires no plugin.

## Architecture

The architecture skills in `.claude/skills/` (`application-layer`, `dependency-injection`, `error-handling`, `validation`, `events`, `testing-architecture`, `domain-layer-cqrs`, `plugin-boundaries`) are the rules for code added here and for the plugins tested in the sandbox. The sandbox itself keeps two console commands with no service layer: they are single-step tools, and the skills say not to add a layer for consistency alone.

## Conventions

- `declare(strict_types=1);` in every PHP file, PSR-12 through php-cs-fixer.
- Names over comments; a comment explains a non-obvious why in one line.
- Console commands are `final` and live in `app/Console`.
- Tests: read the `pest-testing` skill. Feature tests use the `app_test`
  database; unit tests never touch it.
- Secrets: never read, print or commit `.env` or `auth.json`.

## Git

- `main` is protected: never push to it and never force-push. Work on a
  branch named after the change (`feat/…`, `fix/…`, `chore/…`, `docs/…`) and
  merge through a pull request once `make ready` passes.
- Code, comments, commit messages and documentation are written in English.
- Commits: an imperative subject saying what the change does, a body with the
  why.
