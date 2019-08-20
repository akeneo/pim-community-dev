DOCKER_COMPOSE = docker-compose
YARN_EXEC = $(DOCKER_COMPOSE) run --rm node yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u docker --rm fpm php
PHP_EXEC = $(DOCKER_COMPOSE) exec -u docker fpm php
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

.PHONY: clean-front
clean-front:
	rm -rf web/bundles web/dist web/css web/js

node_modules: package.json
	$(YARN_EXEC) install

.PHONY: assets
assets:
	$(PHP_RUN) bin/console --env=prod pim:installer:assets --symlink --clean

.PHONY: css
css:
	$(YARN_EXEC) run less

.PHONY: javascript-cloud
javascript-cloud: docker-compose.override.yml
	$(DOCKER_COMPOSE) run -e EDITION=cloud --rm node yarn run webpack

.PHONY: javascript-prod
javascript-prod: docker-compose.override.yml
	$(YARN_EXEC) run webpack

.PHONY: javascript-dev
javascript-dev: docker-compose.override.yml
	$(YARN_EXEC) run webpack-dev

.PHONY: javascript-test
javascript-test: docker-compose.override.yml
	$(YARN_EXEC) run webpack-test

.PHONY: front
front: clean-front docker-compose.override.yml assets css javascript-test

##
## Back
##

.PHONY: clean-back
clean-back:
	rm -rf var/cache && $(PHP_RUN) bin/console cache:warmup

composer.lock: composer.json
	$(PHP_RUN) /usr/local/bin/composer update

vendor: composer.lock
	$(PHP_RUN) /usr/local/bin/composer install

.PHONY: database
database: docker-compose.override.yml
	$(PHP_RUN) bin/console pim:installer:db

##
## PIM install
##

.PHONY: clean
clean: clean-back clean-front

.PHONY: pim-test
pim-test: vendor node_modules
	APP_ENV=behat $(MAKE) clean
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-test
	$(MAKE) javascript-dev
	APP_ENV=behat $(MAKE) database
	APP_ENV=behat $(PHP_RUN) bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: pim-prod
pim-prod: vendor node_modules
	APP_ENV=prod $(MAKE) clean
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-prod
	APP_ENV=prod $(MAKE) database

.PHONY: all-pims
all-pim: pim-prod pim-test

##
## Docker
##

.PHONY: php-image-dev
php-image-dev:
	DOCKER_BUILDKIT=1 docker build --progress=plain --pull --tag akeneo/pim-dev/php:7.2 --target dev .

.PHONY: php-image-prod
php-image-prod:
	DOCKER_BUILDKIT=1 docker build --progress=plain --pull --tag eu.gcr.io/akeneo-cloud:${IMAGE_TAG} --target prod .

.PHONY: php-images
php-image: php-image-dev php-image-prod

.PHONY: up
up: docker-compose.override.yml
	$(DOCKER_COMPOSE) up -d --remove-orphan

.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v

##
## Deprecated targets
##

behat.yml:
	cp ./behat.yml.dist ./behat.yml
	sed -i "s/127.0.0.1\//httpd-behat\//g" ./behat.yml
	sed -i "s/127.0.0.1/selenium/g" ./behat.yml

docker-compose.override.yml:
	cp docker-compose.override.yml.dist docker-compose.override.yml
