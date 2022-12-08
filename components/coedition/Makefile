COEDITION_PATH ?= components/coedition
COEDITION_BOOTSTRAP_PATH ?= /srv/pim/config/bootstrap.php

.PHONY: coedition-unit-back
coedition-unit-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(COEDITION_PATH)/back/ \
		--bootstrap $(COEDITION_BOOTSTRAP_PATH) \
		--testsuite CoEdition_Unit \
		--log-junit var/tests/phpunit/phpunit_coedition_unit.xml ${O}

.PHONY: coedition-integration-back
coedition-integration-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(COEDITION_PATH)/back/ \
		--bootstrap $(COEDITION_BOOTSTRAP_PATH) \
		--testsuite CoEdition_Integration \
		--log-junit var/tests/phpunit/phpunit_coedition_integration.xml  ${O}
