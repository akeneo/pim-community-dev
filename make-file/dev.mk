# @deprecated Those target are deprecated we keep them because the public api of the makefile is not quite stable

# Please
# - add a new target `bounded-context-unit-back` in `make-file/bounded-context.mk`
# - add it as `unit-back` dependency
# - make sure `unit-back` does not run your tests
#
# Example:
# .PHONY: unit-back
# unit-back: var/tests/phpspec bounded-context-unit-back
.PHONY: phpspec
phpspec:
	${PHP_RUN} vendor/bin/phpspec run ${F}

# Please
# - add a new target `bounded-context-acceptance-back` in `make-file/bounded-context.mk`
# - add it as `acceptance-back` dependency
# - make sure `acceptance-back` does not run your tests
#
# Example:
# .PHONY: unit-back
# acceptance-back: var/tests/phpspec bounded-context-acceptance-back
.PHONY: acceptance
acceptance:
	${PHP_RUN} vendor/bin/behat -p acceptance ${F}

# Please
# - add a new target `bounded-context-integration-back` in `make-file/bounded-context.mk`
# - add it as `integration-back` dependency
# - make sure `integration-back` does not run your tests
#
# Example:
# .PHONY: unit-back
# integration-back: var/tests/phpspec bounded-context-integration-back
.PHONY: phpunit
phpunit:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c phpunit.xml.dist ${F}

# Please use the target `end-to-end-legacy` instead
.PHONY: behat-legacy
behat-legacy:
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat -p legacy -s all ${F}

.PHONY: xdebug-on
xdebug-on:
	XDEBUG_ENABLED=1 $(MAKE) up

.PHONY: xdebug-off
xdebug-off:
	XDEBUG_ENABLED=0 $(MAKE) up
