DOCKER_COMPOSE_RUN_PHP_TEST_ENV = $(DOCKER_COMPOSE) run --rm -e APP_ENV=test php
DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV = $(DOCKER_COMPOSE) run --rm -e APP_ENV=test_fake php

.PHONY: install-front-dependencies-supplier
install-front-dependencies-supplier: #Doc: Install front dependencies for the Supplier part of Onboarder Serenity
	$(YARN_RUN) --cwd=components/onboarder-supplier/front install

.PHONY: lint-back-retailer
lint-back-retailer: #Doc: Run PHPStan and PHPCSFixer for the retailer part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/onboarder-retailer/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/onboarder-retailer/back/tests/.php_cs.php components/onboarder-retailer/back

.PHONY: lint-back-supplier
lint-back-supplier: #Doc: Run PHPStan and PHPCSFixer for the supplier part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/onboarder-supplier/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/onboarder-supplier/back/tests/.php_cs.php components/onboarder-supplier/back

.PHONY: lint-back
lint-back: lint-back-retailer lint-back-supplier #Doc: Run PHPStan and PHPCSFixer for Onboarder Serenity

.PHONY: fix-phpcs-retailer
fix-phpcs-retailer: #Doc: Run PHP-CS-Fixer for the retailer part of Onboarder Serenity
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/onboarder-retailer/back/tests/.php_cs.php components/onboarder-retailer/back

.PHONY: fix-phpcs-supplier
fix-phpcs-supplier: #Doc: Run PHP-CS-Fixer for the supplier part of Onboarder Serenity
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/onboarder-supplier/back/tests/.php_cs.php components/onboarder-supplier/back

.PHONY: fix-phpcs
fix-phpcs: fix-phpcs-retailer fix-phpcs-supplier #Doc: Run PHP-CS-Fixer for Onboarder Serenity

.PHONY: lint-front-retailer
lint-front-retailer: #Doc: Run Prettier and Eslint for the retailer part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-retailer/front lint:check

.PHONY: lint-front-supplier
lint-front-supplier: #Doc: Run Prettier and Eslint for the supplier part of Onboarder Serenity
	$(NODE_RUN) /bin/sh -c "cd components/onboarder-supplier/front" && $(YARN_RUN) --cwd=components/onboarder-supplier/front lint:check

lint-front: lint-front-retailer lint-front-supplier #Doc: Run Prettier and Eslint for Onboarder Serenity

.PHONY: fix-frontcs-retailer
fix-frontcs-retailer: #Doc: Run front fix code style for the retailer part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-retailer/front lint:fix

.PHONY: fix-frontcs-supplier
fix-frontcs-supplier: #Doc: Run front fix code style for the supplier part of Onboarder Serenity
	$(NODE_RUN) /bin/sh -c "cd components/onboarder-supplier/front" && $(YARN_RUN) run --cwd=components/onboarder-supplier/front lint:fix

fix-frontcs: fix-frontcs-retailer fix-frontcs-supplier #Doc: Fix front CS for Onboarder Serenity

.PHONY: coupling-retailer
coupling-retailer: #Doc: Run coupling detector for the retailer part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/onboarder-retailer/back/tests/.php_cd.php components/onboarder-retailer/back

.PHONY: coupling-supplier
coupling-supplier: #Doc: Run coupling detector for the supplier part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/onboarder-supplier/back/tests/.php_cd.php components/onboarder-supplier/back

.PHONY: coupling
coupling: coupling-retailer coupling-supplier #Doc: Run coupling detector for Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=upgrades/.php_cd.php upgrades/schema

.PHONY: coupling-list-unused-requirements-retailer
coupling-list-unused-requirements-retailer: #Doc: List unused coupling detector requirements for the retailer part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/onboarder-retailer/back/tests/.php_cd.php components/onboarder-retailer/back

.PHONY: coupling-list-unused-requirements-supplier
coupling-list-unused-requirements-supplier: #Doc: List unused coupling detector requirements for the supplier part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/onboarder-supplier/back/tests/.php_cd.php components/onboarder-supplier/back

.PHONY: coupling-list-unused-requirements
coupling-list-unused-requirements: coupling-list-unused-requirements-retailer coupling-list-unused-requirements-supplier #Doc: List unused coupling detector requirements for Onboarder Serenity

.PHONY: unit-back-retailer
unit-back-retailer: #Doc: Run unit back tests for the retailer part of Onboarder Serenity
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Retailer_Unit_Test --configuration components/onboarder-retailer/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: unit-back-supplier
unit-back-supplier: #Doc: Run unit back tests for the supplier part of Onboarder Serenity
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Supplier_Unit_Test --configuration components/onboarder-supplier/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: unit-back
unit-back: unit-back-retailer unit-back-supplier #Doc: Run unit back tests for Onboarder Serenity

.PHONY: unit-front-retailer
unit-front-retailer: #Doc: Run unit front tests for the retailer part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-retailer/front test:unit:run

.PHONY: unit-front-supplier
unit-front-supplier: #Doc: Run unit front tests for the supplier part of Onboarder Serenity
	$(NODE_RUN) /bin/sh -c "cd components/onboarder-supplier/front" && $(YARN_RUN) run --cwd=components/onboarder-supplier/front test:unit:run

.PHONY: unit-front
unit-front: unit-front-retailer unit-front-supplier #Doc: Run unit front tests for Onboarder Serenity

.PHONY: acceptance-back-retailer
acceptance-back-retailer: #Doc: Run Behat acceptance back tests for the retailer part of Onboarder Serenity
ifeq ($(CI),true)
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder-retailer/back/tests/behat.yml --profile acceptance --format pim --out var/tests/behat/onboarder-serenity-acceptance --format progress --out std --colors $(O)
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder-retailer/back/tests/behat.yml --profile acceptance ${ARGS}
endif

.PHONY: acceptance-back-supplier
acceptance-back-supplier: #Doc: Run Behat acceptance back tests for the supplier part of Onboarder Serenity
ifeq ($(CI),true)
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder-supplier/back/tests/behat.yml --profile acceptance --format pim --out var/tests/behat/onboarder-serenity-acceptance --format progress --out std --colors $(O)
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder-supplier/back/tests/behat.yml --profile acceptance ${ARGS}
endif

.PHONY: acceptance-back
acceptance-back: acceptance-back-retailer acceptance-back-supplier #Doc: Run acceptance back tests for Onboarder Serenity

.PHONY: integration-back
integration-back: #Doc: Run integration back tests for Onboarder Serenity
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Onboarder_Serenity_Retailer_Integration_Test
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Retailer_Integration_Test --configuration components/onboarder-retailer/back/tests/phpunit.xml.dist ${ARGS}
endif

.PHONY: integration-back-retailer
integration-back-retailer: #Doc: Run integration back tests for the retailer part of Onboarder Serenity
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Onboarder_Serenity_Retailer_Integration_Test
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Retailer_Integration_Test --configuration components/onboarder-retailer/back/tests/phpunit.xml.dist ${ARGS}
endif

.PHONY: integration-back-supplier
integration-back-supplier: #Doc: Run integration back tests for the supplier part of Onboarder Serenity
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Onboarder_Serenity_Supplier_Integration_Test
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Supplier_Integration_Test --configuration components/onboarder-supplier/back/tests/phpunit.xml.dist ${ARGS}
endif

.PHONY: integration-back
integration-back: integration-back-retailer integration-back-supplier #Doc: Run integration back tests for Onboarder Serenity

.PHONY: lint
lint: lint-back lint-front #Doc: Run front and back lint for Onboarder Serenity

.PHONY: tests-onboarder
tests-onboarder: tests-front-onboarder tests-back-onboarder #Doc: Run front and back tests for Onboarder Serenity

.PHONY: tests-back-onboarder
tests-back-onboarder: lint-back coupling coupling-list-unused-requirements unit-back acceptance-back integration-back #Doc: Run back tests for Onboarder Serenity

.PHONY: tests-front-onboarder
tests-front-onboarder: lint-front unit-front #Doc: Run front tests for Onboarder Serenity

tests-onboarder-retailer: lint-front-retailer unit-front-retailer lint-back-retailer coupling-retailer coupling-list-unused-requirements-retailer unit-back-retailer acceptance-back-retailer integration-back-retailer

tests-onboarder-supplier: lint-front-supplier unit-front-supplier lint-back-supplier coupling-supplier coupling-list-unused-requirements-supplier unit-back-supplier acceptance-back-supplier integration-back-supplier

.PHONY: extract-front-translations
extract-front-translations: #Doc: Extract translations for Crowdin
	$(YARN_RUN) run --cwd=components/onboarder-supplier/front i18n-extract 'src/**/*.{ts,tsx}' --ignore '**/*.{test,d}.{ts,tsx}' --format simple --out-file src/translations/messages.en.json

.PHONY: build-onboarder-supplier-front-app
build-onboarder-supplier-front-app: #Doc: Build Onboarder supplier frontend application
	$(YARN_RUN) run --cwd=components/onboarder-supplier/front app:build
