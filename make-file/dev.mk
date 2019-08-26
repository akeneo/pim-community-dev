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
	APP_ENV=behat $(PHP_EXEC) vendor/bin/behat -p legacy ${F}

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

.PHONY: pim-behat
pim-behat:
	APP_ENV=behat C='fpm mysql elasticsearch httpd object-storage selenium' make up
	APP_ENV=behat $(MAKE) clean
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-test
	$(MAKE) javascript-dev
	APP_ENV=behat $(MAKE) database
	APP_ENV=behat $(PHP_RUN) bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

