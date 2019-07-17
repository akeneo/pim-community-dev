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

.env:
	cp .env.dist .env

##
## PIM installation
##

## Dependencies installation
composer.lock: composer.json
	$(PHP_RUN) /usr/local/bin/composer update

vendor: composer.lock
	$(PHP_RUN) /usr/local/bin/composer install

node_modules: package.json yarn.lock
	$(YARN_EXEC) install

## Instal the PIM asset: copy asset from src to web, generate require path, form extension and translation
web/css:
	$(DOCKER_COMPOSE) run -u docker --rm fpm mkdir web/css

web/js:
	$(DOCKER_COMPOSE) run -u docker --rm fpm mkdir web/js

web/css/pim.css: web/css web/js/require-paths.js $(LESS_FILES)
	$(YARN_EXEC) run less

web/js/require-paths.js: web/js $(REQUIRE_JS_FILES)
	$(PHP_EXEC) bin/console pim:installer:dump-require-paths

web/bundles: $(ASSET_FILES)
	$(PHP_EXEC) bin/console assets:install --relative --symlink

web/js/translation:
	$(PHP_EXEC) bin/console oro:translation:dump 'en_US, ca_ES, da_DK, de_DE, es_ES, fi_FI, fr_FR, hr_HR, it_IT, ja_JP, nl_NL, pl_PL, pt_BR, pt_PT, ru_RU, sv_SE, tl_PH, zh_CN, sv_SE, en_NZ'

.PHONY: install-asset
install-asset: vendor node_modules web/bundles web/css/pim.css web/js/require-paths.js web/js/translation
	for locale in $(LOCALE_TO_REFRESH) ; do \
		$(PHP_EXEC) bin/console oro:translation:dump $$locale ; \
	done
	## Prevent translations update next time
	$(DOCKER_COMPOSE) run --rm fpm touch web/js/translation
	$(PHP_EXEC) bin/console fos:js-routing:dump --target web/js/routes.js

## Initialize the PIM database depending on an environment
.PHONY: install-database-test
install-database-test: app/config/parameters_test.yml vendor
	$(PHP_EXEC) bin/console --env=behat pim:installer:db

.PHONY: install-database-prod
install-database-prod: app/config/parameters.yml vendor
	$(PHP_EXEC) bin/console --env=prod pim:installer:db

## Initialize the PIM frontend depending on an environment
.PHONY: build-front-dev
build-front-dev: node_modules install-asset
	$(YARN_EXEC) run webpack-dev

.PHONY: build-front-test
build-front-test: node_modules install-asset
	$(YARN_EXEC) run webpack-test

## Initialize the PIM
.PHONY: install-pim-prod
install-pim-prod: clean app/config/parameters.yml app/config/parameters_test.yml build-front-dev install-database-prod

.PHONY: install-pim-test
install-pim-test: clean app/config/parameters.yml app/config/parameters_test.yml build-front-test install-database-test

.PHONY: install-pim
install-pim: install-pim-test install-pim-prod

##
## Docker
##

.PHONY: pull
pull: .env
	$(DOCKER_COMPOSE) pull

# `make up` will start all container (node, mysql, object storage, selenium, elasticsearch, fpm and http)
# `make up C=fpm` will lonly start the fpm container
.PHONY: up
up: .env
	$(DOCKER_COMPOSE) up -d --remove-orphan ${C}

# `make down` wille stop docker containers, remove volumes and networks
.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v

##
## Back tests
##

.PHONY: coupling-back
coupling-back: structure-coupling user-management-coupling channel-coupling enrichment-coupling
.PHONY: check-pullup-back
check-pullup-back:
	${PHP_EXEC} bin/check-pullup
.PHONY: lint-back
lint-back:
	${PHP_EXEC} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php
	${PHP_EXEC} vendor/bin/phpstan analyse src/Akeneo/Pim -l 1
.PHONY: unit-back
unit-back:
	${PHP_EXEC} vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml
.PHONY: acceptance-back
acceptance-back:
	${PHP_EXEC} vendor/bin/behat --strict -p acceptance -vv

##
## Front tests
##

.PHONY: lint-front
lint-front:
	${YARN_EXEC} run lint
.PHONY: unit-front
unit-front:
	${YARN_EXEC} run unit
.PHONY: acceptance-front
acceptance-front:
	${YARN_EXEC} run acceptance ./tests/features
.PHONY: integration-front
integration-front:
	${YARN_EXEC} run integration