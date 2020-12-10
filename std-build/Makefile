#
# This file is a template Makefile. Some targets are presented here as examples.
# Feel free to customize it to your needs!
#
CMD_ON_PROJECT = docker-compose run -u www-data -e COMPOSER_AUTH -e APP_ENV=$(APP_ENV) --rm php
PHP_RUN = $(CMD_ON_PROJECT) php
YARN_RUN = docker-compose run -u node --rm -e YARN_REGISTRY -e PUPPETEER_SKIP_CHROMIUM_DOWNLOAD node yarn

ifdef NO_DOCKER
  CMD_ON_PROJECT =
  YARN_RUN = yarnpkg
  PHP_RUN = php
endif

APP_ENV := prod_onprem
.DEFAULT_GOAL := dev


yarn.lock: package.json
	PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 $(YARN_RUN) install

node_modules: yarn.lock
	PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=1 $(YARN_RUN) install

.PHONY: dsm
dsm:
	$(YARN_RUN) --cwd=vendor/akeneo/pim-community-dev/akeneo-design-system install --frozen-lockfile
	$(YARN_RUN) --cwd=vendor/akeneo/pim-community-dev/akeneo-design-system run lib:build

.PHONY: assets
assets:
	$(CMD_ON_PROJECT) rm -rf public/bundles public/js
	$(PHP_RUN) bin/console pim:installer:assets --symlink --clean

.PHONY: css
css:
	$(CMD_ON_PROJECT) rm -rf public/css
	$(YARN_RUN) run less

.PHONY: javascript
javascript: dsm
	$(CMD_ON_PROJECT) rm -rf public/dist
ifeq "$(APP_ENV)" "dev"
	$(YARN_RUN) run webpack-dev
else
	$(YARN_RUN) run webpack
endif

.PHONY: front
front: assets css javascript

.PHONY: database
database:
	$(PHP_RUN) bin/console pim:installer:db ${O}

.PHONY: cache
cache:
	$(CMD_ON_PROJECT) rm -rf var/cache && $(PHP_RUN) -d memory_limit=256M bin/console cache:warmup

composer.lock: composer.json
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer update

.PHONY: vendor
vendor: composer.lock
	$(PHP_RUN) -d memory_limit=4G /usr/local/bin/composer install

.PHONY: dependencies
dependencies: vendor node_modules

.PHONY: dev
dev:
	$(MAKE) dependencies APP_ENV=dev
	$(MAKE) pim-dev

.PHONY: prod
prod:
	$(MAKE) dependencies APP_ENV=$(APP_ENV)
	$(MAKE) pim-prod_onprem

.PHONY: pim-prod_onprem
pim-prod_onprem:
pim-prod_onprem: APP_ENV=prod_onprem
pim-prod_onprem: CATALOG=--catalog vendor/akeneo/pim-enterprise-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal
pim-prod_onprem: pim-env

.PHONY: pim-prod_flex
pim-prod_flex:
pim-prod_flex: APP_ENV=prod_flex
pim-prod_flex: CATALOG=--catalog vendor/akeneo/pim-enterprise-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal
pim-prod_flex: pim-env

.PHONY: pim-dev
pim-dev: APP_ENV=dev
pim-dev: CATALOG=--catalog vendor/akeneo/pim-enterprise-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
pim-dev: pim-env

.PHONY: pim-env
pim-env:
ifndef $(NO_DOCKER)
	$(MAKE) up
	docker/wait_docker_up.sh
endif
	$(MAKE) cache APP_ENV=$(APP_ENV)
	$(MAKE) assets APP_ENV=$(APP_ENV)
	$(MAKE) javascript APP_ENV=$(APP_ENV)
	$(MAKE) database O="$(CATALOG)" APP_ENV=$(APP_ENV)

.PHONY: up
up:
	APP_ENV=$(APP_ENV) docker-compose up -d --remove-orphan

.PHONY: down
down:
	docker-compose down -v
