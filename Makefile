DOCKER_COMPOSE = docker-compose
PHP_EXEC = $(DOCKER_COMPOSE) exec -u docker fpm php
YARN_EXEC = $(DOCKER_COMPOSE) run --rm node yarn

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


##
## PIM initialization
##

vendor:
	$(PHP_EXEC) /usr/local/bin/composer install

node_modules: package.json
	$(YARN_EXEC) install

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

## Clean backend cache
.PHONY: clean
clean:
	rm -rf var/cache

## Start docker containers
.PHONY: up
up: .env docker-compose.override.yml app/config/parameters.yml app/config/parameters_test.yml
	$(DOCKER_COMPOSE) up -d --remove-orphan

## Initialize the PIM: install database (behat/prod) and run webpack
.PHONY: init-pim
init-pim: clean docker-compose.override.yml app/config/parameters.yml app/config/parameters_test.yml vendor node_modules
	$(PHP_EXEC) bin/console --env=prod pim:install --force --symlink --clean
	$(PHP_EXEC) bin/console --env=behat pim:installer:db
	$(YARN_EXEC) run webpack-dev
	$(YARN_EXEC) run webpack-test

## Stop docker containers, remove volumes and networks
.PHONY: down
down:
	$(DOCKER_COMPOSE) down -v


##
## Run tests suite
##

## Run the coupling detector on everything
.PHONY: coupling
coupling: twa-coupling asset-coupling franklin-insights-coupling reference-entity-coupling rule-engine-coupling workflow-coupling permission-coupling

