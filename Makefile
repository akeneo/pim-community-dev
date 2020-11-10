DOCKER_COMPOSE = docker-compose
NODE_RUN = $(DOCKER_COMPOSE) run -u node --rm -e YARN_REGISTRY -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 -e PUPPETEER_EXECUTABLE_PATH=/usr/bin/google-chrome node
YARN_RUN = $(NODE_RUN) yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u www-data --rm php php
PHP_EXEC = $(DOCKER_COMPOSE) exec -u www-data fpm php
IMAGE_TAG ?= master
CI ?= 0

.DEFAULT_GOAL := help

.PHONY: help
help: #Doc: This help
	@echo "$$(grep -hE '^\S+:.*#Doc:' $(MAKEFILE_LIST) | sed -e 's/:.*#Doc:\s*/:/' -e 's/^\(.\+\):\(.*\)/\\e[1;1m \1\\e[0m:\2/' | column -c2 -t -s :)"

## Include all *.mk files
include make-file/*.mk

##
## Front
##

yarn.lock: package.json #Doc: perform “yarn install”
	$(YARN_RUN) install

node_modules: yarn.lock #Doc: perform “yarn install --check-files”
	$(YARN_RUN) install --frozen-lockfile --check-files

.PHONY: dsm
dsm:
	$(YARN_RUN) --cwd=vendor/akeneo/pim-community-dev/akeneo-design-system install --frozen-lockfile
	$(YARN_RUN) --cwd=vendor/akeneo/pim-community-dev/akeneo-design-system run lib:build

.PHONY: assets
assets:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/bundles public/js
	$(PHP_RUN) bin/console --env=prod pim:installer:assets --symlink --clean

.PHONY: css
css:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf public/css
	$(YARN_RUN) run less

.PHONY: javascript-prod
javascript-prod: dsm
	$(NODE_RUN) rm -rf public/dist
	$(DOCKER_COMPOSE) run -e EDITION=cloud --rm node yarn run webpack

.PHONY: javascript-prod-onprem-paas
javascript-prod-onprem-paas: dsm
	$(NODE_RUN) rm -rf public/dist
	$(YARN_RUN) run webpack

.PHONY: javascript-dev
javascript-dev: dsm
	$(NODE_RUN) rm -rf public/dist
	$(YARN_RUN) run webpack-dev

.PHONY: javascript-dev-strict
javascript-dev-strict: dsm
	$(NODE_RUN) rm -rf public/dist
	$(YARN_RUN) run webpack-dev --strict

.PHONY: javascript-test
javascript-test: dsm
	$(NODE_RUN) rm -rf public/dist
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

composer.lock: composer.json
	$(PHP_RUN) -d memory_limit=5G /usr/local/bin/composer update --no-interaction

vendor: composer.lock
	$(PHP_RUN) -d memory_limit=5G /usr/local/bin/composer install --no-interaction

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
pim-behat: #Doc: build, (re)create, start containers, clear symfony cache, load front env, launch js tests, load the db and create an admin user
	APP_ENV=behat $(MAKE) up
	APP_ENV=behat $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-test
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=behat $(MAKE) database
	APP_ENV=behat $(PHP_RUN) bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: pim-test
pim-test: #Doc: build, (re)create, start containers, clear symfony cache and load the db
	APP_ENV=test $(MAKE) up
	APP_ENV=test $(MAKE) cache
	docker/wait_docker_up.sh
	APP_ENV=test $(MAKE) database

.PHONY: pim-dev
pim-dev: #Doc: build, (re)create, start containers, clear symfony cache, load front env and load the demo db
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=dev O="--catalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev" $(MAKE) database

.PHONY: pim-prod
pim-prod: #Doc: build, (re)create, start containers, clear symfony cache, load front env and load the db
	APP_ENV=prod $(MAKE) up
	APP_ENV=prod $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-cloud
	docker/wait_docker_up.sh
	APP_ENV=prod $(MAKE) database

.PHONY: pim-saas-like
pim-saas-like: export COMPOSE_PROJECT_NAME = pim-saas-like
pim-saas-like: export COMPOSE_FILE = docker-compose.saas-like.yml
pim-saas-like: #Doc: build, (re)create, start containers, load the db and create an admin user
	$(DOCKER_COMPOSE) up --detach --remove-orphan
	docker/wait_docker_up.sh
	$(DOCKER_COMPOSE) run fpm bin/console pim:installer:db
	$(DOCKER_COMPOSE) run fpm bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: down-pim-saas-like
down-pim-saas-like: export COMPOSE_PROJECT_NAME = pim-saas-like
down-pim-saas-like: export COMPOSE_FILE = docker-compose.saas-like.yml
down-pim-saas-like:
	$(DOCKER_COMPOSE) down

##
## Docker
##

.PHONY: php-image-dev
php-image-dev: #Doc: pull docker for pim-enterprise-dev with the dev tag
	DOCKER_BUILDKIT=1 docker build --progress=plain --pull --tag akeneo/pim-dev/php:7.4 --target dev .

.PHONY: php-image-prod
php-image-prod: #Doc: pull docker for pim-enterprise-dev with the prod tag
ifeq ($(CI),true)
	git config user.name "Michel Tag"
	git remote set-url origin https://micheltag:${MICHEL_TAG_TOKEN}@github.com/akeneo/pim-enterprise-dev.git
endif
	sed -i "s/VERSION = '.*';/VERSION = '${IMAGE_TAG_DATE}';/g" src/Akeneo/Platform/EnterpriseVersion.php
	git add src/Akeneo/Platform/EnterpriseVersion.php
	git commit -m "Prepare SaaS ${IMAGE_TAG}"

ifeq ($(CI),true)
	DOCKER_BUILDKIT=1 docker build --no-cache --progress=plain --pull --tag eu.gcr.io/akeneo-ci/pim-enterprise-dev:${IMAGE_TAG} --target prod --build-arg COMPOSER_AUTH='${COMPOSER_AUTH}' .
else
	DOCKER_BUILDKIT=1 docker build --no-cache --progress=plain --pull --tag eu.gcr.io/akeneo-ci/pim-enterprise-dev:${IMAGE_TAG} --target prod .
endif

.PHONY: push-php-image-prod
push-php-image-prod: #Doc: push docker image to docker hub
	docker push eu.gcr.io/akeneo-ci/pim-enterprise-dev:${IMAGE_TAG}

.PHONY: up
up: #Doc: build, (re)create and start containers
	$(DOCKER_COMPOSE) up -d --remove-orphan ${C}

.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v
