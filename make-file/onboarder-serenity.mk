DOCKER_COMPOSE_RUN_PHP_TEST_ENV = $(DOCKER_COMPOSE) run --rm -e APP_ENV=test php
DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV = $(DOCKER_COMPOSE) run --rm -e APP_ENV=test_fake php

.PHONY: front-dependencies-supplier
front-dependencies-supplier:
	$(YARN_RUN) --cwd=components/onboarder-supplier/front install

.PHONY: lint-back-retailer
lint-back-retailer: #Doc: Run PHPStan and PHPCSFixer for the retailer part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/onboarder-retailer/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/onboarder-retailer/back/tests/.php_cs.php components/onboarder-retailer/back

.PHONY: lint-back
lint-back: lint-back-retailer #Doc: Run PHPStan and PHPCSFixer for Onboarder Serenity

.PHONY: fix-phpcs-retailer
fix-phpcs-retailer: #Doc: Run PHP-CS-Fixer for the retailer part of Onboarder Serenity
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/onboarder-retailer/back/tests/.php_cs.php components/onboarder-retailer/back

.PHONY: fix-phpcs
fix-phpcs: fix-phpcs-retailer #Doc: Run PHP-CS-Fixer for Onboarder Serenity

.PHONY: lint-front-retailer
lint-front-retailer: #Doc: Run Prettier and Eslint for the retailer part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-retailer/front lint:check

.PHONY: lint-front-supplier
lint-front-supplier: #Doc: Run Prettier and Eslint for the supplier part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-supplier/front lint:check

lint-front: lint-front-retailer lint-front-supplier #Doc: Run Prettier and Eslint for Onboarder Serenity

.PHONY: fix-frontcs-retailer
fix-frontcs-retailer: #Doc: Run front fix code style for the retailer part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-retailer/front lint:fix

.PHONY: fix-frontcs-supplier
fix-frontcs-supplier: #Doc: Run front fix code style for the supplier part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-supplier/front lint:fix

fix-frontcs: fix-frontcs-retailer fix-frontcs-supplier #Doc: Fix front CS for Onboarder Serenity

.PHONY: coupling-retailer
coupling-retailer: #Doc: Run coupling detector for the retailer part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/onboarder-retailer/back/tests/.php_cd.php components/onboarder-retailer/back

.PHONY: coupling
coupling: coupling-retailer #Doc: Run coupling detector for Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=upgrades/.php_cd.php upgrades/schema

.PHONY: coupling-list-unused-requirements-retailer
coupling-list-unused-requirements-retailer: #Doc: List unused coupling detector requirements for the retailer part of Onboarder Serenity
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/onboarder-retailer/back/tests/.php_cd.php components/onboarder-retailer/back

.PHONY: coupling-list-unused-requirements
coupling-list-unused-requirements: coupling-list-unused-requirements-retailer #Doc: List unused coupling detector requirements for Onboarder Serenity

.PHONY: unit-back
unit-back: #Doc: Run unit back tests for Onboarder Serenity
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Retailer_Unit_Test --configuration components/onboarder-retailer/back/tests/phpunit.xml.dist ${ARGS}

.PHONY: unit-front-retailer
unit-front-retailer: #Doc: Run unit front tests for the retailer part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-retailer/front test:unit:run

.PHONY: unit-front-supplier
unit-front-supplier: #Doc: Run unit front tests for the supplier part of Onboarder Serenity
	$(YARN_RUN) run --cwd=components/onboarder-supplier/front test:unit:run

.PHONY: unit-front
unit-front: unit-front-retailer unit-front-supplier #Doc: Run unit front tests for Onboarder Serenity

.PHONY: acceptance-back
acceptance-back: #Doc: Run Behat acceptance back tests for Onboarder Serenity
ifeq ($(CI),true)
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder-retailer/back/tests/behat.yml --profile acceptance --format pim --out var/tests/behat/onboarder-serenity-acceptance --format progress --out std --colors $(O)
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_FAKE_ENV) vendor/bin/behat --config components/onboarder-retailer/back/tests/behat.yml --profile acceptance ${ARGS}
endif

.PHONY: integration-back
integration-back: #Doc: Run integration back tests for Onboarder Serenity
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Onboarder_Serenity_Retailer_Integration_Test
else
	$(DOCKER_COMPOSE_RUN_PHP_TEST_ENV) vendor/bin/phpunit --testsuite Onboarder_Serenity_Retailer_Integration_Test --configuration components/onboarder-retailer/back/tests/phpunit.xml.dist ${ARGS}
endif

.PHONY: lint
lint: lint-back lint-front #Doc: Run front and back lint for Onboarder Serenity

.PHONY: tests-onboarder
tests-onboarder: tests-front-onboarder tests-back-onboarder #Doc: Run front and back tests for Onboarder Serenity

.PHONY: tests-back-onboarder
tests-back-onboarder: lint-back coupling coupling-list-unused-requirements unit-back acceptance-back integration-back #Doc: Run back tests for Onboarder Serenity

.PHONY: tests-front-onboarder
tests-front-onboarder: lint-front unit-front #Doc: Run front tests for Onboarder Serenity

.PHONY: extract-front-translations
extract-front-translations: #Doc: Extract translations for Crowdin
	$(YARN_RUN) run --cwd=components/onboarder-supplier/front i18n-extract 'src/**/*.{ts,tsx}' --ignore '**/*.{test,d}.{ts,tsx}' --format simple --out-file src/translations/messages.en.json
