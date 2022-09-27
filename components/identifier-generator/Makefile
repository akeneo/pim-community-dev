IDENTIFIER_GENERATOR_PATH ?= components/identifier-generator
IDENTIFIER_GENERATOR_BOOTSTRAP_PATH ?= /srv/pim/config/bootstrap.php
IDENTIFIER_GENERATOR_YARN = $(YARN_RUN)

# TESTS BACK
.PHONY: identifier-generator-integration-back
identifier-generator-integration-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(IDENTIFIER_GENERATOR_PATH)/back/ \
		--bootstrap $(IDENTIFIER_GENERATOR_BOOTSTRAP_PATH) \
		--testsuite Identifier_Generator_Integration \
		--order-by random \
		--log-junit var/tests/phpunit/phpunit_identifier_generator_integration.xml ${O}

# TESTS FRONT
.PHONY: identifier-generator-unit-front
identifier-generator-unit-front: yarn-policies
	$(IDENTIFIER_GENERATOR_YARN) workspace @akeneo-pim-community/identifier-generator test:unit:run --ci --coverage
