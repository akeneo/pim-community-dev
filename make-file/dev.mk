##
## Run tests suite
##

.PHONY: coupling
coupling: twa-coupling asset-coupling franklin-insights-coupling reference-entity-coupling rule-engine-coupling workflow-coupling permission-coupling

.PHONY: phpspec
phpspec:
	XDEBUG_ENABLED=0 ${PHP_RUN} vendor/bin/phpspec run ${F}

.PHONY: phpspec-debug
phpspec-debug:
	XDEBUG_ENABLED=1 ${PHP_RUN} vendor/bin/phpspec run ${F}

.PHONY: behat-acceptance
behat-acceptance: behat.yml
	XDEBUG_ENABLED=0 ${PHP_RUN} vendor/bin/behat -p acceptance ${F}

.PHONY: behat-acceptance-debug
behat-acceptance-debug: behat.yml
	XDEBUG_ENABLED=1 ${PHP_RUN} vendor/bin/behat -p acceptance ${F}

.PHONY: phpunit
phpunit:
	XDEBUG_ENABLED=0 ${PHP_RUN} vendor/bin/phpunit -c phpunit.xml.dist ${F}

.PHONY: phpunit-debug
phpunit-debug:
	XDEBUG_ENABLED=1 ${PHP_RUN} vendor/bin/phpunit -c phpunit.xml.dist ${F}

.PHONY: behat-legacy
behat-legacy: behat.yml
	$(DOCKER_COMPOSE) exec -u docker -e APP_ENV=behat fpm php vendor/bin/behat -p legacy ${F}

##
## Xdebug
##

## Enable Xdebug
.PHONY: xdebug-on
xdebug-on:
	XDEBUG_ENABLED=1 $(MAKE) up

## Disable Xdebug
.PHONY: xdebug-off
xdebug-off:
	XDEBUG_ENABLED=0 $(MAKE) up
