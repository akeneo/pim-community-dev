DOCKER_COMPOSE = docker-compose
NODE_RUN = $(DOCKER_COMPOSE) run -u node --rm node
YARN_RUN = $(NODE_RUN) yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u www-data --rm php php
PHP_EXEC = $(DOCKER_COMPOSE) exec -u www-data fpm php

yarn.lock: package.json
	$(YARN_RUN) install

node_modules: yarn.lock
	$(YARN_RUN) install --frozen-lockfile
	$(YARN_RUN) check --integrity

.PHONY: assets
assets:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/bundles public/js
	$(PHP_RUN) bin/console --env=prod pim:installer:assets --symlink --clean

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

.PHONY: javascript-test
javascript-test:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/dist
	$(YARN_RUN) run webpack-test

.PHONY: front
front: assets css javascript-test javascript-dev

.PHONY: cache
cache:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache && $(PHP_RUN) bin/console cache:warmup

composer.lock: composer.json
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer update

vendor: composer.lock
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer install
.PHONY: dependencies
dependencies: vendor node_modules

.PHONY: pim
pim:
	$(MAKE) up
	$(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	$(MAKE) database O="--catalog vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev"

