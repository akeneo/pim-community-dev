##
## Run tests suite
##

.PHONY: coupling
coupling: twa-coupling asset-coupling franklin-insights-coupling reference-entity-coupling rule-engine-coupling workflow-coupling permission-coupling

.PHONY: phpspec
phpspec:
	${PHP_RUN} vendor/bin/phpspec run ${F}

.PHONY: acceptance
acceptance: behat.yml
	${PHP_RUN} vendor/bin/behat -p acceptance ${F}

.PHONY: phpunit
phpunit:
	${PHP_RUN} vendor/bin/phpunit -c phpunit.xml.dist ${F}

.PHONY: end-to-end
end-to-end: behat.yml
	APP_ENV=behat $(PHP_EXEC) vendor/bin/behat -p legacy -s all ${F}

##
## Xdebug
##

## Enable Xdebug
.PHONY: xdebug-on
xdebug-on: docker-compose.override.yml
	XDEBUG_ENABLED=1 $(MAKE) up

## Disable Xdebug
.PHONY: xdebug-off
xdebug-off: docker-compose.override.yml
	XDEBUG_ENABLED=0 $(MAKE) up

