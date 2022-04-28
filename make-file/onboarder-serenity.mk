DOCKER_COMPOSE_RUN_PHP_TEST_ENV = $(DOCKER_COMPOSE) run -u www-data --rm -e APP_ENV=test php
DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV = $(DOCKER_COMPOSE) run -u www-data --rm -e APP_ENV=test_fake php

.PHONY: lint-back
lint-back: #Doc: Run PHPStan and PHPCSFixer for Onboarder Serenity
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/onboarder/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/onboarder/back/tests/.php_cs.php components/onboarder/back

.PHONY: fix-phpcs
fix-phpcs: #Doc: Run PHP-CS-Fixer for Onboarder Serenity
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/onboarder/back/tests/.php_cs.php components/onboarder/back

lint-front: #Doc: Run Prettier and Eslint for Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder/front lint:check

fix-frontcs: #Doc: Fix front CS for Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder/front lint:fix

.PHONY: coupling
coupling: #Doc: Run coupling detector for Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/onboarder/back/tests/.php_cd.php components/onboarder/back
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=upgrades/.php_cd.php upgrades/schema

.PHONY: coupling-list-unused-requirements
coupling-list-unused-requirements: #Doc: List unused coupling detector requirements for Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/onboarder/back/tests/.php_cd.php components/onboarder/back

.PHONY: unit-back
unit-back: #Doc: Run unit back tests for Onboarder Serenity
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Unit_Test --configuration components/onboarder/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: unit-front
unit-front: #Doc: Run unit front tests for Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder/front test:unit:run

.PHONY: acceptance-back
acceptance-back: #Doc: Run Behat acceptance back tests for Onboarder Serenity
ifeq ($(CI),true)
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder/back/tests/behat.yml --profile acceptance --format pim --out var/tests/behat/onboarder-serenity-acceptance --format progress --out std --colors $(O)
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder/back/tests/behat.yml --profile acceptance ${ARGS}
endif

.PHONY: integration-back
integration-back: #Doc: Run integration back tests for Onboarder Serenity
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Onboarder_Serenity_Integration_Test
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Integration_Test --configuration components/onboarder/back/tests/phpunit.xml.dist ${ARGS}
endif

.PHONY: lint
lint: lint-back lint-front #Doc: Run front and back lint for Onboarder Serenity

.PHONY: tests-onboarder
tests-onboarder: tests-front-onboarder tests-back-onboarder #Doc: Run front and back tests for Onboarder Serenity

.PHONY: tests-back-onboarder
tests-back-onboarder: lint-back coupling coupling-list-unused-requirements unit-back acceptance-back integration-back #Doc: Run back tests for Onboarder Serenity

.PHONY: tests-front-onboarder
tests-front-onboarder: lint-front unit-front #Doc: Run front tests for Onboarder Serenity
