DOCKER_COMPOSE = docker-compose
YARN_EXEC = $(DOCKER_COMPOSE) run -u node --rm node yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u www-data --rm php php
PHP_EXEC = $(DOCKER_COMPOSE) exec -u www-data fpm php
IMAGE_TAG ?= master

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

.PHONY: remove-front-files
remove-front-files:
	rm -rf public/bundles public/dist public/css public/js

node_modules: package.json
	$(YARN_EXEC) install

.PHONY: assets
assets:
	$(PHP_RUN) bin/console --env=prod pim:installer:assets --symlink --clean

.PHONY: css
css:
	$(YARN_EXEC) run less

.PHONY: javascript-prod
javascript-prod:
	$(YARN_EXEC) run webpack

.PHONY: javascript-dev
javascript-dev:
	$(YARN_EXEC) run webpack-dev

.PHONY: javascript-test
javascript-test:
	$(YARN_EXEC) run webpack-test

.PHONY: front
front: remove-front-files assets css javascript-test javascript-dev

##
## Back
##

.PHONY: fix-cs-back
fix-cs-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php

.PHONY: cache
cache:
	rm -rf var/cache && $(PHP_RUN) bin/console cache:warmup

composer.lock: composer.json
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer update

vendor: composer.lock
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer install

.PHONY: database
database:
	$(PHP_RUN) bin/console pim:installer:db

##
## PIM install
##

.PHONY: dependencies
dependencies: vendor node_modules

.PHONY: pim-behat
pim-behat:
	APP_ENV=behat $(MAKE) up
	APP_ENV=behat $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-test
	$(MAKE) javascript-dev
	APP_ENV=behat $(MAKE) database
	APP_ENV=behat $(PHP_RUN) bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: pim-test
pim-test:
	APP_ENV=test $(MAKE) up
	APP_ENV=test $(MAKE) cache
	APP_ENV=test $(MAKE) database

.PHONY: pim-dev
pim-dev:
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-dev
	APP_ENV=dev $(MAKE) database

.PHONY: pim-prod
pim-prod:
	APP_ENV=prod $(MAKE) up
	APP_ENV=prod $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-prod
	APP_ENV=prod $(MAKE) database

##
## Docker
##

.PHONY: php-image-dev
php-image-dev:
	DOCKER_BUILDKIT=1 docker build --progress=plain --pull --tag akeneo/pim-dev/php:7.3 --target dev .

.PHONY: php-image-prod
php-image-prod:
	DOCKER_BUILDKIT=1 docker build --progress=plain --pull --tag eu.gcr.io/akeneo-cloud:${IMAGE_TAG} --target prod .

.PHONY: php-images
php-images: php-image-dev php-image-prod

.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d --remove-orphan ${C}

.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v
