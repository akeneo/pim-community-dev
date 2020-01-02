#
# This file is a template Makefile. Some targets are presented here as examples.
# Feel free to customize it to your needs!
#
CMD_ON_PROJECT = docker-compose run -u www-data --rm php
PHP_RUN = $(CMD_ON_PROJECT) php
YARN_RUN = docker-compose run -u node --rm node yarn

ifdef NO_DOCKER
  CMD_ON_PROJECT =
  YARN_RUN = yarnpkg
  PHP_RUN = php
endif

.DEFAULT_GOAL := dev

yarn.lock: package.json
	$(YARN_RUN) install

node_modules: yarn.lock
	$(YARN_RUN) install

.PHONY: assets
assets:
	$(CMD_ON_PROJECT) rm -rf public/bundles public/js
	$(PHP_RUN) bin/console pim:installer:assets --symlink --clean

.PHONY: css
css:
	$(CMD_ON_PROJECT) rm -rf public/css
	$(YARN_RUN) run less

.PHONY: javascript-prod
javascript-prod:
	$(CMD_ON_PROJECT) rm -rf public/dist
	$(YARN_RUN) run webpack

.PHONY: javascript-dev
javascript-dev:
	$(CMD_ON_PROJECT) rm -rf public/dist
	$(YARN_RUN) run webpack-dev

.PHONY: front
front: assets css javascript-dev

.PHONY: database
database:
	$(PHP_RUN) bin/console pim:installer:db ${O}

.PHONY: cache
cache:
	$(CMD_ON_PROJECT) rm -rf var/cache && $(PHP_RUN) bin/console cache:warmup

composer.lock: composer.json
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer update

vendor: composer.lock
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer install

.PHONY: dependencies
dependencies: vendor node_modules

.PHONY: dev
dev:
	$(MAKE) dependencies
	$(MAKE) pim-dev

.PHONY: prod
prod:
	$(MAKE) dependencies
	$(MAKE) pim-prod

.PHONY: pim-prod
pim-prod:
ifndef NO_DOCKER
	APP_ENV=prod $(MAKE) up
	docker/wait_docker_up.sh
endif
	$(MAKE) cache
	$(MAKE) assets
	$(MAKE) javascript-prod
	APP_ENV=prod $(MAKE) database O="--catalog vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal"

.PHONY: pim-dev
pim-dev:
ifndef NO_DOCKER
	APP_ENV=dev $(MAKE) up
	docker/wait_docker_up.sh
endif
	$(MAKE) cache
	$(MAKE) assets
	$(MAKE) javascript-dev
	APP_ENV=dev $(MAKE) database O="--catalog vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev"

.PHONY: up
up:
	docker-compose up -d --remove-orphan

.PHONY: down
down:
	docker-compose down -v

