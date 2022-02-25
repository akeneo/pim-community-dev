DOCKER_COMPOSE_RUN_PHP = $(DOCKER_COMPOSE) run -u www-data --rm php
DOCKER_COMPOSE_RUN_PHP_TEST_ENV = $(DOCKER_COMPOSE) run -u www-data --rm -e APP_ENV=test php

.PHONY: onboarder-unit-tests
onboarder-unit-tests:
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Unit_Test --configuration components/onboarder/back/tests/phpunit.xml.dist ${IO}

.PHONY: onboarder-integration-tests
onboarder-integration-tests:
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Integration_Test --configuration components/onboarder/back/tests/phpunit.xml.dist ${IO}
