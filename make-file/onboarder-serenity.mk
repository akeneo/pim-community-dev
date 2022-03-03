DOCKER_COMPOSE_RUN_PHP_TEST_ENV = $(DOCKER_COMPOSE) run -u www-data --rm -e APP_ENV=test php

.PHONY: unit-back
unit-back: #Doc: Run unit back tests for Onboarder
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Unit_Test --configuration components/onboarder/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: integration
integration: #Doc: Run integration tests for Onboarder
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Integration_Test --configuration components/onboarder/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: coupling
coupling: #Doc: Run coupling detector for Onboarder
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/onboarder/back/tests/.php_cd.php components/onboarder/back

.PHONY: coupling-list-unused-requirements
coupling-list-unused-requirements: #Doc: List unused coupling detector requirements
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/onboarder/back/tests/.php_cd.php components/onboarder/back

.PHONY: lint-back
lint-back: #Doc: Run PHPStan for Onboarder Serenity
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/onboarder/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/onboarder/back/tests/.php_cs.php components/onboarder/back

.PHONY: fix-lint-back
fix-lint-back: #Doc: Run PHP-CS-Fixer for Onboarder Serenity
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/onboarder/back/tests/.php_cs.php components/onboarder/back
