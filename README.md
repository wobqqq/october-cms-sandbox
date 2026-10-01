# October CMS sandbox

An empty [October CMS](https://octobercms.com) 4.4 install for installing and
trying out plugins: the core modules, a minimal theme with a home page and the
error pages, a local administrator, and the same checks the plugins run
(Pest, PHPStan at level max, php-cs-fixer, Rector).

Plugins come in two ways:

- **linked** from a checkout that sits next to the sandbox (`../oc-fortify-plugin`),
  so every change in the checkout is live;
- **required** with composer, from the October marketplace or Packagist, the way
  a site installs them.

## Requirements

- Docker with Compose v2 and `make`.
- An October CMS license: `auth.json` with the project's gateway credentials
  (copy `auth.example.json` and fill it in). It is git-ignored.

## Quick start

```bash
cp auth.example.json auth.json   # then add the license
make install                     # .env, containers, composer install, migrations, admin
```

| What | Where |
|------|-------|
| Site | http://localhost:8082 |
| Backend | http://localhost:8082/admin (`admin` / `password`) |
| HTTPS (self-signed) | https://localhost:8443 |
| Mailpit | http://localhost:8027 (SMTP `localhost:1027`) |
| MySQL | `127.0.0.1:33062`, user `root`, no password, databases `app` and `app_test` |

Every port binds to `127.0.0.1` and can be changed in `.env` (`APP_PORT`,
`APP_PORT_HTTPS`, `MAILPIT_UI_PORT`, `MAILPIT_SMTP_PORT`, `DB_PUBLISHED_PORT`).

## Installing a plugin

### From a local checkout

```bash
make plugin.link PLUGIN=../oc-fortify-plugin
make plugin.unlink PLUGIN=../oc-fortify-plugin
make plugin.links               # what is linked now
make plugins.fortify            # the Fortify core and its five modules
make plugins.fortify.unlink
```

`bin/plugin` mounts the checkout at `plugins/<vendor>/<name>` inside the
containers (named after the namespace in its `Plugin.php`), runs
`october:migrate` and clears the cache. The mounts live in
`docker-compose.override.yml`, which the script writes and removes; the empty
mount points under `plugins/` are all you see on the host.

- The checkout must sit in the same folder as the sandbox: the whole folder is
  mounted at `/var/www/portfolio`.
- The checkout's own `vendor/` is hidden inside the containers. October loads a
  plugin's `vendor/autoload.php`, and a checkout's development packages (Pest,
  Testbench, its October test stubs) would clash with the site's.
- It is a mount and not a symlink because October refuses to read a file whose
  real path is outside the project (`system.restrict_base_dir`).
- Unlinking rolls back the plugin's migrations with `sandbox:rollback` and never
  touches the checkout. Do not run `php artisan plugin:remove` on a linked
  plugin: it deletes the plugin's files.

### With composer

```bash
make plugin.require PACKAGE=wobqqq/fortify-plugin
make plugin.remove PACKAGE=wobqqq/fortify-plugin
```

The gateway repository serves October and marketplace plugins; anything else
comes from Packagist. `plugin.remove` rolls the plugin back before
`composer remove`.

## Commands

| Command | Does |
|---------|------|
| `make docker.up` / `docker.down` / `docker.rebuild` | Start, stop, rebuild the containers |
| `make shell` | A shell in the php-fpm container |
| `make migrate` | `october:migrate` |
| `make fresh` | Drop every table, migrate, create the administrator |
| `make admin` | Create the administrator or reset its password (`sandbox:admin`) |
| `make code.fix` | composer normalize, php-cs-fixer, Rector |
| `make code.check` | validate, normalize, audit, yaml-lint, php-cs-fixer, Rector, PHPStan |
| `make test` / `make test.coverage` | Pest, with coverage of `app/` (minimum 90 %) |
| `make ready` | `code.fix`, `code.check` and `test.coverage` |

The tests run against `app_test`, never the working database, and check the
plugins installed at the time: link a plugin and `make test` tells you whether
the site and the backend still work with it.

## Layout

| Path | Holds |
|------|-------|
| `app/Provider.php` | The application plugin: the `backend_url()` Twig function and the sandbox commands |
| `app/Console` | `sandbox:admin`, `sandbox:rollback` |
| `themes/sandbox` | The theme: `home`, `404`, `error` and `maintenance` pages |
| `bin/plugin` | Links and unlinks plugin checkouts |
| `docker/` | nginx, PHP 8.4 FPM and the MySQL init script |
| `tests/` | Pest: architecture, theme, backend, commands, installed plugins |

## Continuous integration

There is none on purpose: the October modules come from the licensed gateway,
so a runner would need the license. Run `make ready` locally.

## License

MIT, see [LICENSE](LICENSE). October CMS itself is licensed separately.
