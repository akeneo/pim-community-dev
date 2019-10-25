##
## Run tests suite
##

.PHONY: coupling
coupling: structure-coupling user-management-coupling channel-coupling enrichment-coupling

.PHONY: phpspec
phpspec:
	${PHP_RUN} vendor/bin/phpspec run ${F}

.PHONY: acceptance
acceptance:
	${PHP_RUN} vendor/bin/behat -p acceptance ${F}

.PHONY: phpunit
phpunit:
	${PHP_RUN} vendor/bin/phpunit -c phpunit.xml.dist ${F}

.PHONY: behat-legacy
behat-legacy:
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat -p legacy -s all ${F}

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

