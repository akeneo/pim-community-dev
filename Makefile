DOCKER_COMPOSE = docker-compose
PHP_EXEC = $(DOCKER_COMPOSE) exec fpm php
YARN_EXEC = $(DOCKER_COMPOSE) run --rm node yarn

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo ""
	@echo "Akeneo Pim Community Dev available targets:"
	@echo ""
	@grep -E '^.PHONY:.*##.*' $(MAKEFILE_LIST) | cut -c9- | sort | awk 'BEGIN {FS = " ## "}; {printf "%-30s %s\n", $$1, $$2}'

composer.lock: composer.json
	$(PHP_EXEC) /usr/local/bin/composer update

vendor: composer.lock
	$(PHP_EXEC) /usr/local/bin/composer install

node_modules: package.json
	$(YARN_EXEC) install

behat.yml:
	cp ./behat.yml.dist ./behat.yml
	sed -i "s/127.0.0.1\//httpd-behat\//g" ./behat.yml
	sed -i "s/127.0.0.1/selenium/g" ./behat.yml

app/config/parameters.yml:
	cp ./app/config/parameters.yml.dist ./app/config/parameters.yml
	sed -i "s/database_host:.*localhost/database_host: mysql/g" ./app/config/parameters.yml
	sed -i "s/localhost: 9200/elastic:changeme@elasticsearch:9200/g" ./app/config/parameters.yml

app/config/parameters_test.yml:
	cp ./app/config/parameters_test.yml.dist ./app/config/parameters_test.yml
	sed -i "s/database_host:.*localhost/database_host:                        mysql-behat/g" ./app/config/parameters_test.yml
	sed -i "s/localhost: 9200/elastic:changeme@elasticsearch:9200/g" ./app/config/parameters_test.yml
	sed -i "s/product_index_name:.*akeneo_pim_product/product_index_name:                    test_akeneo_pim_product/g" ./app/config/parameters_test.yml
	sed -i "s/product_model_index_name:.*akeneo_pim_product_model/product_model_index_name:              test_akeneo_pim_product_model/g" ./app/config/parameters_test.yml
	sed -i "s/product_and_product_model_index_name:.*akeneo_pim_product_and_product_model/product_and_product_model_index_name:  test_akeneo_pim_product_and_product_model/g" ./app/config/parameters_test.yml

docker-compose.yml:
	cp docker-compose.yml.dist docker-compose.yml

.PHONY: clean ## Clean backend cache
clean:
	rm -rf var/cache

.PHONY: init-pim ## Install the database
init-pim: clean docker-compose.yml app/config/parameters.yml app/config/parameters_test.yml vendor node_modules
	$(PHP_EXEC) bin/console --env=prod pim:install --force --symlink --clean
	$(PHP_EXEC) bin/console --env=behat pim:installer:db
	$(YARN_EXEC) run webpack-dev
	$(YARN_EXEC) run webpack-test

.PHONY: up ## Start docker containers
up: docker-compose.yml app/config/parameters.yml app/config/parameters_test.yml
	$(DOCKER_COMPOSE) up -d --remove-orphan

.PHONY: xdebug-on-ui
xdebug-on-ui: docker-compose.yml
	sed -i "s/XDEBUG_ENABLED: 0/XDEBUG_ENABLED: 1/g" docker-compose.yml
	make up

.PHONY: xdebug-off-ui
xdebug-off-ui: docker-compose.yml
	sed -i "s/XDEBUG_ENABLED: 1/XDEBUG_ENABLED: 0/g" docker-compose.yml
	make up

.PHONY: xdebug-on-cli
xdebug-on-cli: up
	${DOCKER_COMPOSE} exec fpm phpenmod xdebug

.PHONY: xdebug-off-cli
xdebug-off-cli: up
	${DOCKER_COMPOSE} exec fpm phpdismod xdebug


.PHONY: down ## Stop docker containers, remove volumes and networks.
down:
	$(DOCKER_COMPOSE) down -v
