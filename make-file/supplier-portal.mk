DOCKER_COMPOSE_RUN_PHP_TEST_ENV = $(DOCKER_COMPOSE) run --rm -e APP_ENV=test php
DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV = $(DOCKER_COMPOSE) run --rm -e APP_ENV=test_fake php

.PHONY: install-front-dependencies-supplier
install-front-dependencies-supplier: #Doc: Install front dependencies for the Supplier part of Supplier Portal
	$(YARN_RUN) --cwd=components/supplier-portal-supplier/front install

.PHONY: lint-back-retailer
lint-back-retailer: #Doc: Run PHPStan and PHPCSFixer for the retailer part of Supplier Portal
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/supplier-portal-retailer/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/supplier-portal-retailer/back/tests/.php_cs.php components/supplier-portal-retailer/back

.PHONY: lint-back-supplier
lint-back-supplier: #Doc: Run PHPStan and PHPCSFixer for the supplier part of Supplier Portal
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/supplier-portal-supplier/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/supplier-portal-supplier/back/tests/.php_cs.php components/supplier-portal-supplier/back

.PHONY: lint-back
lint-back: lint-back-retailer lint-back-supplier #Doc: Run PHPStan and PHPCSFixer for Supplier Portal

.PHONY: fix-phpcs-retailer
fix-phpcs-retailer: #Doc: Run PHP-CS-Fixer for the retailer part of Supplier Portal
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/supplier-portal-retailer/back/tests/.php_cs.php components/supplier-portal-retailer/back

.PHONY: fix-phpcs-supplier
fix-phpcs-supplier: #Doc: Run PHP-CS-Fixer for the supplier part of Supplier Portal
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/supplier-portal-supplier/back/tests/.php_cs.php components/supplier-portal-supplier/back

.PHONY: fix-phpcs
fix-phpcs: fix-phpcs-retailer fix-phpcs-supplier #Doc: Run PHP-CS-Fixer for Supplier Portal

.PHONY: lint-front-retailer
lint-front-retailer: #Doc: Run Prettier and Eslint for the retailer part of Supplier Portal
	$(YARN_RUN) run --cwd=components/supplier-portal-retailer/front lint:check

.PHONY: lint-front-supplier
lint-front-supplier: #Doc: Run Prettier and Eslint for the supplier part of Supplier Portal
	$(NODE_RUN) /bin/sh -c "cd components/supplier-portal-supplier/front" && $(YARN_RUN) --cwd=components/supplier-portal-supplier/front lint:check

lint-front: lint-front-retailer lint-front-supplier #Doc: Run Prettier and Eslint for Supplier Portal

.PHONY: fix-frontcs-retailer
fix-frontcs-retailer: #Doc: Run front fix code style for the retailer part of Supplier Portal
	$(YARN_RUN) run --cwd=components/supplier-portal-retailer/front lint:fix

.PHONY: fix-frontcs-supplier
fix-frontcs-supplier: #Doc: Run front fix code style for the supplier part of Supplier Portal
	$(NODE_RUN) /bin/sh -c "cd components/supplier-portal-supplier/front" && $(YARN_RUN) run --cwd=components/supplier-portal-supplier/front lint:fix

fix-frontcs: fix-frontcs-retailer fix-frontcs-supplier #Doc: Fix front CS for Supplier Portal

.PHONY: coupling-retailer
coupling-retailer: coupling-list-unused-requirements-retailer #Doc: Run coupling detector for the retailer part of Supplier Portal
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/supplier-portal-retailer/back/tests/.php_cd.php components/supplier-portal-retailer/back

.PHONY: coupling-supplier
coupling-supplier: coupling-list-unused-requirements-supplier #Doc: Run coupling detector for the supplier part of Supplier Portal
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/supplier-portal-supplier/back/tests/.php_cd.php components/supplier-portal-supplier/back

.PHONY: coupling
coupling: coupling-retailer coupling-supplier coupling-list-unused-requirements #Doc: Run coupling detector for Supplier Portal
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=upgrades/.php_cd.php upgrades/schema

.PHONY: coupling-list-unused-requirements-retailer
coupling-list-unused-requirements-retailer: #Doc: List unused coupling detector requirements for the retailer part of Supplier Portal
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/supplier-portal-retailer/back/tests/.php_cd.php components/supplier-portal-retailer/back

.PHONY: coupling-list-unused-requirements-supplier
coupling-list-unused-requirements-supplier: #Doc: List unused coupling detector requirements for the supplier part of Supplier Portal
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/supplier-portal-supplier/back/tests/.php_cd.php components/supplier-portal-supplier/back

.PHONY: coupling-list-unused-requirements
coupling-list-unused-requirements: coupling-list-unused-requirements-retailer coupling-list-unused-requirements-supplier #Doc: List unused coupling detector requirements for Supplier Portal

.PHONY: unit-back-retailer
unit-back-retailer: #Doc: Run unit back tests for the retailer part of Supplier Portal
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Supplier_Portal_Retailer_Unit_Test --configuration components/supplier-portal-retailer/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: unit-back-supplier
unit-back-supplier: #Doc: Run unit back tests for the supplier part of Supplier Portal
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Supplier_Portal_Supplier_Unit_Test --configuration components/supplier-portal-supplier/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: unit-back
unit-back: unit-back-retailer unit-back-supplier #Doc: Run unit back tests for Supplier Portal

.PHONY: unit-front-retailer
unit-front-retailer: #Doc: Run unit front tests for the retailer part of Supplier Portal
	$(YARN_RUN) run --cwd=components/supplier-portal-retailer/front test:unit:run

.PHONY: unit-front-supplier
unit-front-supplier: #Doc: Run unit front tests for the supplier part of Supplier Portal
	$(NODE_RUN) /bin/sh -c "cd components/supplier-portal-supplier/front" && $(YARN_RUN) run --cwd=components/supplier-portal-supplier/front test:unit:run

.PHONY: unit-front
unit-front: unit-front-retailer unit-front-supplier #Doc: Run unit front tests for Supplier Portal

.PHONY: acceptance-back-retailer
acceptance-back-retailer: #Doc: Run Behat acceptance back tests for the retailer part of Supplier Portal
ifeq ($(CI),true)
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/supplier-portal-retailer/back/tests/behat.yml --profile acceptance --format pim --out var/tests/behat/supplier-portal-acceptance --format progress --out std --colors $(O)
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/supplier-portal-retailer/back/tests/behat.yml --profile acceptance ${ARGS}
endif

.PHONY: acceptance-back-supplier
acceptance-back-supplier: #Doc: Run Behat acceptance back tests for the supplier part of Supplier Portal
ifeq ($(CI),true)
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/supplier-portal-supplier/back/tests/behat.yml --profile acceptance --format pim --out var/tests/behat/supplier-portal-acceptance --format progress --out std --colors $(O)
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/supplier-portal-supplier/back/tests/behat.yml --profile acceptance ${ARGS}
endif

.PHONY: acceptance-back
acceptance-back: acceptance-back-retailer acceptance-back-supplier #Doc: Run acceptance back tests for Supplier Portal

.PHONY: integration-back-retailer
integration-back-retailer: #Doc: Run integration back tests for the retailer part of Supplier Portal
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh components/supplier-portal-retailer/back/tests/phpunit.xml.dist vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Supplier_Portal_Retailer_Integration_Test
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Supplier_Portal_Retailer_Integration_Test --configuration components/supplier-portal-retailer/back/tests/phpunit.xml.dist ${ARGS}
endif

.PHONY: integration-back-supplier
integration-back-supplier: #Doc: Run integration back tests for the supplier part of Supplier Portal
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh components/supplier-portal-supplier/back/tests/phpunit.xml.dist vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Supplier_Portal_Supplier_Integration_Test
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Supplier_Portal_Supplier_Integration_Test --configuration components/supplier-portal-supplier/back/tests/phpunit.xml.dist ${ARGS}
endif

.PHONY: integration-back
integration-back: integration-back-retailer integration-back-supplier #Doc: Run integration back tests for Supplier Portal

.PHONY: lint
lint: lint-back lint-front #Doc: Run front and back lint for Supplier Portal

.PHONY: tests-supplier-portal
tests-supplier-portal: tests-front-supplier-portal tests-back-supplier-portal #Doc: Run front and back tests for Supplier Portal

.PHONY: tests-back-supplier-portal
tests-back-supplier-portal: lint-back coupling coupling-list-unused-requirements unit-back acceptance-back integration-back #Doc: Run back tests for Supplier Portal

.PHONY: tests-front-supplier-portal
tests-front-supplier-portal: lint-front unit-front #Doc: Run front tests for Supplier Portal

tests-supplier-portal-retailer: lint-front-retailer unit-front-retailer lint-back-retailer coupling-retailer coupling-list-unused-requirements-retailer unit-back-retailer acceptance-back-retailer integration-back-retailer

tests-supplier-portal-supplier: lint-front-supplier unit-front-supplier lint-back-supplier coupling-supplier coupling-list-unused-requirements-supplier unit-back-supplier acceptance-back-supplier integration-back-supplier

.PHONY: build-supplier-portal-supplier-front-app
build-supplier-portal-supplier-front-app: #Doc: Build Supplier Portal supplier frontend application
	$(YARN_RUN) run --cwd=components/supplier-portal-supplier/front app:build
	mkdir public/supplier-portal/
	mv components/supplier-portal-supplier/front/build/* public/supplier-portal/

.PHONY: trans-front-extract-supplier
trans-front-extract-supplier: #Doc: Extract Supplier App translations for Crowdin
	$(YARN_RUN) run --cwd=components/supplier-portal-supplier/front i18n-extract 'src/**/*.{ts,tsx}' --ignore '**/*.d.ts' --format simple --out-file translations/messages.en_US.json

.PHONY: test-migrations
test-migrations: #Doc: Execute SP integration tests for migrations
	APP_ENV=test $(PHP_RUN) ./vendor/bin/phpunit -c . --testsuite PIM_Migration_Test --group migration-supplier-portal
