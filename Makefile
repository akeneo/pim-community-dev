DOCKER_COMPOSE = docker-compose
NODE_RUN = $(DOCKER_COMPOSE) run -u node --rm -e YARN_REGISTRY -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome node
YARN_RUN = $(NODE_RUN) yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u www-data --rm php php
PHP_EXEC = $(DOCKER_COMPOSE) exec -u www-data fpm php
IMAGE_TAG ?= master
CI ?= 0
PIM_CONTEXT ?=0

.DEFAULT_GOAL := help

.PHONY: help
help: #Doc: display this help
	@echo "$$(grep -hE '^\S+:.*#Doc:' $(MAKEFILE_LIST) | sed -e 's/:.*#Doc:\s*/:/' -e 's/^\(.\+\):\(.*\)/\1:-\ \2/' | column -c2 -t -s :)"

## Include all *.mk files
ifneq ($(PIM_CONTEXT),0)
include make-file/$(PIM_CONTEXT).mk
endif

##
## Front
##

yarn.lock: package.json #Doc: run YARN install
	$(YARN_RUN) install

node_modules: yarn.lock #Doc: run YARN install --check-files
	$(YARN_RUN) install --frozen-lockfile --check-files

.PHONY: javascript-extensions
javascript-extensions:
	$(YARN_RUN) run update-extensions

.PHONY: dsm
dsm: #Doc: install & build the PIM DSM
	$(YARN_RUN) --cwd=vendor/akeneo/pim-community-dev/akeneo-design-system install --frozen-lockfile
	$(YARN_RUN) --cwd=vendor/akeneo/pim-community-dev/akeneo-design-system run lib:build

.PHONY: assets
assets: #Doc: clean & reinstall assets
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/bundles public/js
	$(PHP_RUN) bin/console pim:installer:assets --symlink --clean

.PHONY: css
css: #Doc: build PIM CSS
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/css
	$(YARN_RUN) run less

.PHONY: javascript-prod
javascript-prod: javascript-extensions #Doc: clean & yarn run webpack in production environement
	$(NODE_RUN) rm -rf public/dist
	$(DOCKER_COMPOSE) run -e EDITION=cloud --rm node yarn run webpack

.PHONY: javascript-prod-onprem-paas
javascript-prod-onprem-paas: javascript-extensions #Doc: clean & yarn run webpack
	$(NODE_RUN) rm -rf public/dist
	$(YARN_RUN) run webpack

.PHONY: javascript-dev
javascript-dev: javascript-extensions #Doc: clean & run webpack dev
	$(NODE_RUN) rm -rf public/dist
	$(YARN_RUN) run webpack-dev

.PHONY: javascript-dev-strict
javascript-dev-strict: javascript-extensions #Doc: clean & run webpack dev --strict
	$(NODE_RUN) rm -rf public/dist
	$(YARN_RUN) run webpack-dev --strict

.PHONY: javascript-test
javascript-test: javascript-extensions #Doc: clean & run webpack test
	$(NODE_RUN) rm -rf public/dist
	$(YARN_RUN) run webpack-test

.PHONY: front
front: assets css dsm javascript-dev #Doc: build the front-end

##
## Back
##

.PHONY: fix-cs-back
fix-cs-back: #Doc: launch CSFixer on the back-end
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php

var/cache/dev: #Doc: create Sf cache in DEV environement
	APP_ENV=dev make cache

.PHONY: cache
cache: #Doc: clean, generate & warm the Sf cache up
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache && $(PHP_RUN) bin/console cache:warmup

composer.lock: composer.json #Doc: launch composer update
	$(PHP_RUN) -d memory_limit=5G /usr/local/bin/composer update --no-interaction

vendor: composer.lock #Doc: run composer install
	$(PHP_RUN) -d memory_limit=5G /usr/local/bin/composer install --no-interaction

.PHONY: check-requirements
check-requirements: #Doc: check if PIM requirements are set
	$(PHP_RUN) bin/console pim:installer:check-requirements

.PHONY: database
database: #Doc: install a new icecat catalog database
	$(PHP_RUN) bin/console pim:installer:db ${O}

##
## PIM install
##

.PHONY: dependencies
dependencies: vendor node_modules #Doc: install PHP & JS dependencies

# Those targets ease the pim installation depending on the Symfony environnement: behat, test, dev, prod.
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
pim-behat: #Doc: run docker-compose up, clean symfony cache, reinstall assets, build PIM CSS, run YARN webpack-test, run webpack dev & install shared_catalog_fixtures database in behat environement
	APP_ENV=behat $(MAKE) up
	APP_ENV=behat $(MAKE) cache
	APP_ENV=behat $(MAKE) assets
	$(MAKE) css
	$(MAKE) dsm
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=behat $(MAKE) database
	APP_ENV=behat $(PHP_RUN) bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: pim-test
pim-test: #Doc: run docker-compose up, clean symfony cache & install a new icecat catalog database in test environement
	APP_ENV=test $(MAKE) up
	APP_ENV=test $(MAKE) cache
	docker/wait_docker_up.sh
	APP_ENV=test $(MAKE) database

.PHONY: pim-dev
pim-dev: #Doc: run docker-compose up, clean symfony cache, run webpack dev & install icecat_demo_dev database in dev environement
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	APP_ENV=dev $(MAKE) assets
	$(MAKE) css
	$(MAKE) dsm
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=dev O="--catalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev" $(MAKE) database

.PHONY: pim-prod
pim-prod: #Doc: run docker-compose up, clean symfony cache, reinstall assets, build PIM CSS, ???run make javascript-cloud??? & install a new icecat catalog database in prod environement
	APP_ENV=prod $(MAKE) up
	APP_ENV=prod $(MAKE) cache
	APP_ENV=prod $(MAKE) assets
	$(MAKE) css
	$(MAKE) dsm
	$(MAKE) javascript-prod
	docker/wait_docker_up.sh
	APP_ENV=prod $(MAKE) database

.PHONY: pim-saas-like
pim-saas-like: export COMPOSE_PROJECT_NAME = pim-saas-like
pim-saas-like: export COMPOSE_FILE = docker-compose.saas-like.yml
pim-saas-like: #Doc: run docker-compose up, install PIM database and create PIM admin user
	$(DOCKER_COMPOSE) up --detach --remove-orphan
	docker/wait_docker_up.sh
	$(DOCKER_COMPOSE) run fpm bin/console pim:installer:db
	$(DOCKER_COMPOSE) run fpm bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: down-pim-saas-like
down-pim-saas-like: export COMPOSE_PROJECT_NAME = pim-saas-like
down-pim-saas-like: export COMPOSE_FILE = docker-compose.saas-like.yml
down-pim-saas-like: #Doc: shutdown all docker containers
	$(DOCKER_COMPOSE) down

##
## Docker
##

.PHONY: php-image-dev
php-image-dev: #Doc: pull docker image for pim-enterprise-dev with the dev tag
	DOCKER_BUILDKIT=1 docker build --progress=plain --pull --tag akeneo/pim-dev/php:7.4 --target dev .

.PHONY: up
up: #Doc: run docker-compose up
	$(DOCKER_COMPOSE) up -d --remove-orphan ${C}

.PHONY: down
down: #Doc: shutdown all docker containers
	$(DOCKER_COMPOSE) down -v
