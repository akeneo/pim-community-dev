##
## Run tests suite
##

# @deprecated Those target are deprecated we keep them because the public api of the makefile is not quite stable

# Please
# - add a new target `bounded-context-unit-back` in `make-file/bounded-context.mk`
# - add it as `unit-back` dependency
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
#
# Example:
# .PHONY: unit-back
# integration-back: var/tests/phpspec bounded-context-integration-back
.PHONY: phpunit
phpunit:
	${PHP_RUN} vendor/bin/phpunit -c phpunit.xml.dist ${F}

# Please
# - add a new target `bounded-context-behat-legacy` in `make-file/bounded-context.mk`
# - add it as `behat-legacy` dependency
#
# Example:
# .PHONY: unit-back
# behat-legacy: var/tests/phpspec bounded-context-behat-legacy
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

