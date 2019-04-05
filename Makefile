DOCKER_COMPOSE = docker-compose
YARN_EXEC = $(DOCKER_COMPOSE) run --rm node yarn
PHP_RUN = $(DOCKER_COMPOSE) run -u docker --rm fpm php
PHP_EXEC = $(DOCKER_COMPOSE) exec -u docker fpm php

LESS_FILES=$(shell find web/bundles -name "*.less")
REQUIRE_JS_FILES=$(shell find . -name "requirejs.yml")
FORM_EXTENSION_FILES=$(shell find . -name "form_extensions.yml")
TRANSLATION_FILES=$(shell find . -name "jsmessages*.yml")
ASSET_FILES=$(shell find . -path "*/Resources/public/*")
LOCALE_TO_REFRESH=$(shell find . -newer web/js/translation  -name "jsmessages*.yml" | grep -o '[a-zA-Z]\{2\}_[a-zA-Z]\{2\}')

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo ""
	@echo "Caution: those targets are optimized for docker"
	@echo ""
	@echo "Please add your custom Makefile in the directory "make-file". They will be automatically loaded!"
	@echo ""

## Include all *.mk files
include make-file/*.mk

## Clean backend cache
.PHONY: clean
clean:
	rm -rf var/cache

##
## PIM configuration
##

behat.yml:
	cp ./behat.yml.dist ./behat.yml
	sed -i "s/127.0.0.1\//httpd-behat\//g" ./behat.yml
	sed -i "s/127.0.0.1/selenium/g" ./behat.yml

app/config/parameters.yml:
	cp ./app/config/parameters.yml.dist ./app/config/parameters.yml
	# Sed commands should be removed when env var will be introduce in the PIM
	sed -i "s/database_host:.*localhost/database_host: mysql/g" ./app/config/parameters.yml
	sed -i "s/localhost: 9200/elastic:changeme@elasticsearch:9200/g" ./app/config/parameters.yml

app/config/parameters_test.yml:
	cp ./app/config/parameters_test.yml.dist ./app/config/parameters_test.yml
	# Sed commands should be removed when env var will be introduce in the PIM
	sed -i "s/database_host:.*localhost/database_host:                        mysql-behat/g" ./app/config/parameters_test.yml
	sed -i "s/localhost: 9200/elastic:changeme@elasticsearch:9200/g" ./app/config/parameters_test.yml
	sed -i "s/product_index_name:.*akeneo_pim_product/product_index_name:                    test_akeneo_pim_product/g" ./app/config/parameters_test.yml
	sed -i "s/product_model_index_name:.*akeneo_pim_product_model/product_model_index_name:              test_akeneo_pim_product_model/g" ./app/config/parameters_test.yml
	sed -i "s/product_and_product_model_index_name:.*akeneo_pim_product_and_product_model/product_and_product_model_index_name:  test_akeneo_pim_product_and_product_model/g" ./app/config/parameters_test.yml
	sed -i "s/record_index_name:.*akeneo_referenceentity_record/record_index_name:                     test_akeneo_referenceentity_record/g" ./app/config/parameters_test.yml
	sed -i "s/product_proposal_index_name:.*akeneo_pim_product_proposal/product_proposal_index_name:           test_akeneo_pim_product_proposal/g" ./app/config/parameters_test.yml
	sed -i "s/published_product_index_name:.*akeneo_pim_published_product/published_product_index_name:          test_akeneo_pim_published_product/g" ./app/config/parameters_test.yml
	sed -i "s/published_product_and_product_model_index_name:.*akeneo_pim_published_product_and_product_model/published_product_and_product_model_index_name: test_akeneo_pim_published_product_and_product_model/g" ./app/config/parameters_test.yml

docker-compose.override.yml:
	cp docker-compose.override.yml.dist docker-compose.override.yml

.env:
	cp .env.dist .env

## Remove all configuration file generated
.PHONY: reset-conf
reset-conf:
	rm .env docker-compose.override.yml app/config/parameters_test.yml app/config/parameters.yml behat.yml

##
## PIM installation
##

composer.lock: composer.json
	$(PHP_RUN) /usr/local/bin/composer update

vendor: composer.lock
	$(PHP_RUN) /usr/local/bin/composer install

node_modules: package.json
	$(YARN_EXEC) install

web/css/pim.css: $(LESS_FILES)
	$(YARN_EXEC) run less

web/js/require-paths.js: $(REQUIRE_JS_FILES)
	$(PHP_EXEC) bin/console pim:installer:dump-require-paths

web/bundles: $(ASSET_FILES)
	$(PHP_EXEC) bin/console assets:install --relative --symlink

web/js/translation:
	$(PHP_EXEC) bin/console oro:translation:dump 'en_US, ca_ES, da_DK, de_DE, es_ES, fi_FI, fr_FR, hr_HR, it_IT, ja_JP, nl_NL, pl_PL, pt_BR, pt_PT, ru_RU, sv_SE, tl_PH, zh_CN, sv_SE, en_NZ'

## Instal the PIM asset: copy asset from src to web, generate require path, form extension and translation
.PHONY: install-asset
install-asset: vendor node_modules web/bundles web/css/pim.css web/js/require-paths.js  web/js/translation
	for locale in $(LOCALE_TO_REFRESH) ; do \
		$(PHP_EXEC) bin/console oro:translation:dump $$locale ; \
	done
	## Prevent translations update next time
	touch web/js/translation
	$(PHP_EXEC) bin/console fos:js-routing:dump --target web/js/routes.js

## Initialize the PIM database depending on an environment
.PHONY: install-database-test
install-database-test: docker-compose.override.yml app/config/parameters_test.yml vendor
	$(PHP_EXEC) bin/console --env=behat pim:installer:db

.PHONY: install-database-prod
install-database-prod: docker-compose.override.yml app/config/parameters.yml vendor
	$(PHP_EXEC) bin/console --env=prod pim:installer:db

## Initialize the PIM frontend depending on an environment
.PHONY: build-front-dev install-asset
build-front-dev: docker-compose.override.yml node_modules
	$(YARN_EXEC) run webpack-dev

.PHONY: build-front-test install-asset
build-front-test: docker-compose.override.yml node_modules
	$(YARN_EXEC) run webpack-test

## Initialize the PIM: install database (behat/prod) and run webpack
.PHONY: install-pim
install-pim: app/config/parameters.yml app/config/parameters_test.yml vendor node_modules clean install-asset build-front-dev build-front-test install-database-test install-database-prod

##
## Docker
##

## Start docker containers
.PHONY: up
up: .env docker-compose.override.yml app/config/parameters.yml app/config/parameters_test.yml
	$(DOCKER_COMPOSE) up -d --remove-orphan

## Stop docker containers, remove volumes and networks
.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v

##
## Xdebug
##

## Enable Xdebug
.PHONY: xdebug-on
xdebug-on: docker-compose.override.yml
	PHP_XDEBUG_ENABLED=1 $(MAKE) up

## Disable Xdebug
.PHONY: xdebug-off
xdebug-off: docker-compose.override.yml
	PHP_XDEBUG_ENABLED=0 $(MAKE) up

##
## Run tests suite
##

.PHONY: coupling
coupling: twa-coupling asset-coupling franklin-insights-coupling reference-entity-coupling rule-engine-coupling workflow-coupling permission-coupling

.PHONY: phpspec
phpspec: vendor
	PHP_XDEBUG_ENABLED=0 ${PHP_RUN} vendor/bin/phpspec run ${F}

.PHONY: phpspec-debug
phpspec-debug: vendor
	PHP_XDEBUG_ENABLED=1 ${PHP_RUN} vendor/bin/phpspec run ${F}

.PHONY: behat-acceptance
behat-acceptance: behat.yml app/config/parameters_test.yml vendor
	PHP_XDEBUG_ENABLED=0 ${PHP_RUN} vendor/bin/behat -p acceptance ${F}

.PHONY: behat-acceptance-debug
behat-acceptance-debug: behat.yml app/config/parameters_test.yml vendor
	PHP_XDEBUG_ENABLED=1 ${PHP_RUN} vendor/bin/behat -p acceptance ${F}

.PHONY: phpunit
phpunit: app/config/parameters_test.yml vendor
	${PHP_EXEC} vendor/bin/phpunit -c app ${F}

.PHONY: behat-legacy
behat-legacy: behat.yml app/config/parameters_test.yml vendor node_modules
	${PHP_EXEC} vendor/bin/behat -p legacy ${F}

