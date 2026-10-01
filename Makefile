SHELL := /bin/bash

PHP := docker compose exec -T php-fpm
FORTIFY := ../oc-fortify-plugin ../oc-fortify-admin-ip-access-plugin ../oc-fortify-ip-blocker-plugin \
	../oc-fortify-smart-ip-blocker-plugin ../oc-fortify-csp-plugin ../oc-fortify-input-sanitizer-plugin

.PHONY: docker.up docker.down docker.rebuild shell \
	install env migrate fresh admin clear \
	code.fix code.check code.stan code.ide test test.coverage ready \
	plugin.link plugin.unlink plugin.links plugin.require plugin.remove plugins.fortify plugins.fortify.unlink

docker.up:
	docker compose up -d --wait

docker.down:
	docker compose down

docker.rebuild:
	docker compose down
	docker compose up -d --build --wait

shell:
	docker compose exec php-fpm sh

env:
	@test -f .env || { cp .env.example .env && echo "Created .env"; }
	@test -f auth.json || { echo "auth.json is missing: copy auth.example.json and add the October CMS license" >&2; exit 1; }

install: env docker.up
	$(PHP) composer install
	@grep -q '^APP_KEY=.' .env || $(PHP) php artisan key:generate
	$(PHP) php artisan october:migrate
	$(PHP) php artisan sandbox:admin

migrate:
	$(PHP) php artisan october:migrate

fresh:
	$(PHP) php artisan db:wipe --force
	$(PHP) php artisan october:migrate
	$(PHP) php artisan cache:clear
	$(PHP) php artisan sandbox:admin

admin:
	$(PHP) php artisan sandbox:admin

clear:
	$(PHP) php artisan cache:clear
	$(PHP) php artisan config:clear

code.fix:
	$(PHP) composer code.fix

code.check:
	$(PHP) composer code.check

code.stan:
	$(PHP) composer code.stan

code.ide:
	$(PHP) composer code.ide

test:
	$(PHP) composer test

test.coverage:
	$(PHP) composer test.coverage

ready:
	$(PHP) composer ready

plugin.link:
	@test -n "$(PLUGIN)" || { echo "Usage: make plugin.link PLUGIN=../oc-fortify-plugin" >&2; exit 1; }
	bin/plugin link $(PLUGIN)

plugin.unlink:
	@test -n "$(PLUGIN)" || { echo "Usage: make plugin.unlink PLUGIN=../oc-fortify-plugin" >&2; exit 1; }
	bin/plugin unlink $(PLUGIN)

plugin.links:
	@bin/plugin list

plugin.require:
	@test -n "$(PACKAGE)" || { echo "Usage: make plugin.require PACKAGE=vendor/name-plugin" >&2; exit 1; }
	$(PHP) composer require $(PACKAGE)
	$(PHP) php artisan october:migrate

plugin.remove:
	@test -n "$(PACKAGE)" || { echo "Usage: make plugin.remove PACKAGE=vendor/name-plugin" >&2; exit 1; }
	$(PHP) php artisan sandbox:rollback $(PACKAGE)
	$(PHP) composer remove $(PACKAGE)

plugins.fortify:
	@for plugin in $(FORTIFY); do bin/plugin link $$plugin || exit 1; done

plugins.fortify.unlink:
	@for plugin in $(filter-out ../oc-fortify-plugin,$(FORTIFY)) ../oc-fortify-plugin; do bin/plugin unlink $$plugin || exit 1; done
