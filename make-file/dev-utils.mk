docker-compose.override.yml:
	cp docker-compose.override.yml.dist docker-compose.override.yml

##
## Run tests
##

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

##
## Xdebug
##

.PHONY: xdebug-on
xdebug-on: docker-compose.override.yml
	PHP_XDEBUG_ENABLED=1 $(MAKE) up

.PHONY: xdebug-off
xdebug-off: docker-compose.override.yml
	PHP_XDEBUG_ENABLED=0 $(MAKE) up

## Remove all configuration file generated
.PHONY: reset-conf
reset-conf:
	rm .env docker-compose.override.yml app/config/parameters_test.yml app/config/parameters.yml behat.yml