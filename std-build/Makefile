DOCKER_COMPOSE = docker-compose
NODE_RUN = $(DOCKER_COMPOSE) run -u node --rm node
YARN_RUN = $(NODE_RUN) yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u www-data --rm php php

.DEFAULT_GOAL := dev

yarn.lock: package.json
	$(YARN_RUN) install

node_modules: yarn.lock
	$(YARN_RUN) install --frozen-lockfile
	$(YARN_RUN) check --integrity

.PHONY: assets
assets:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/bundles public/js
	$(PHP_RUN) bin/console pim:installer:assets --symlink --clean

.PHONY: css
css:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/css
	$(YARN_RUN) run less

.PHONY: javascript-prod
javascript-prod:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/dist
	$(YARN_RUN) run webpack

.PHONY: javascript-dev
javascript-dev:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/dist
	$(YARN_RUN) run webpack-dev

.PHONY: front
front: assets css javascript-dev

.PHONY: database
database:
	$(PHP_RUN) bin/console pim:installer:db ${O}

.PHONY: cache
cache:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache && $(PHP_RUN) bin/console cache:warmup

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

.PHONY: pim-prod
pim-prod:
	APP_ENV=prod $(MAKE) up
	$(MAKE) cache
	$(MAKE) assets
	$(MAKE) javascript-prod
	docker/wait_docker_up.sh
	APP_ENV=prod $(MAKE) database O="--catalog vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal"

.PHONY: pim-dev
pim-dev:
	APP_ENV=dev $(MAKE) up
	$(MAKE) cache
	$(MAKE) assets
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=dev $(MAKE) database O="--catalog vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev"

.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d --remove-orphan

.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v

