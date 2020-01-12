DOCKER_COMPOSE = docker-compose
NODE_RUN = $(DOCKER_COMPOSE) run -u node --rm node
YARN_RUN = $(NODE_RUN) yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u www-data --rm php php
PHP_EXEC = $(DOCKER_COMPOSE) exec -u www-data fpm php

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo ""
	@echo "Caution: those targets are optimized for docker 19+"
	@echo ""
	@echo "Please add your custom Makefile in the directory "make-file". They will be automatically loaded!"
	@echo ""

## Include all *.mk files
include make-file/*.mk

##
## Front
##
.PHONY: node_modules
node_modules:
	$(YARN_RUN) install --frozen-lockfile

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

##
## Back
##

.PHONY: fix-cs-back
fix-cs-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php

var/cache/dev:
	APP_ENV=dev make cache

.PHONY: cache
cache:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache && $(PHP_RUN) bin/console cache:warmup

.PHONY: vendor
vendor:
    # check if composer.json is out of sync with composer.lock
	$(PHP_RUN) /usr/local/bin/composer validate --no-check-all
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer install

.PHONY: check-requirements
check-requirements:
	$(PHP_RUN) bin/console pim:installer:check-requirements

.PHONY: database
database:
	$(PHP_RUN) bin/console pim:installer:db ${O}

##
## PIM install
##

.PHONY: dependencies
dependencies: vendor node_modules

# Those targets ease the pim installation depending the Symfony environnement: behat, test, dev, prod.
#
# For instance :
# If you need to debug a legacy behat please run `make pim-behat` before debugging
# If you need to debug a phpunit please run `make pim-test` before debugging
# If you want to use the PIM with the debug mode enabled please run `make pim-dev` to initialize the PIM
#
# Caution:
# - Make sure your back and front dependencies are up to date (make dependencies).
# - Make sure the docker php is built (make php-image-dev).

.PHONY: pim-behat
pim-behat:
	APP_ENV=behat $(MAKE) up
	APP_ENV=behat $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=behat $(MAKE) database
	APP_ENV=behat $(PHP_RUN) bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: pim-test
pim-test:
	APP_ENV=test $(MAKE) up
	APP_ENV=test $(MAKE) cache
	docker/wait_docker_up.sh
	APP_ENV=test $(MAKE) database

.PHONY: pim-dev
pim-dev:
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=dev O="--catalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev" $(MAKE) database

.PHONY: pim-prod
pim-prod:
	APP_ENV=prod $(MAKE) up
	APP_ENV=prod $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-prod
	docker/wait_docker_up.sh
	APP_ENV=prod $(MAKE) database

.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d --remove-orphan ${C}

.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v
