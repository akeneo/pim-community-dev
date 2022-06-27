CATALOGS_PATH ?= components/catalogs
CATALOGS_BOOTSTRAP_PATH ?= /srv/pim/config/bootstrap.php
CATALOGS_YARN = $(YARN_RUN)

ifeq ($(CI),true)
	BEHAT_ARGS += --no-snippets --no-interaction
	PHPINSIGHTS_ARGS += --summary
endif
ifneq ($(CI),true)
	PSALM_ARGS += --show-info=true
endif
ifneq ($(CI),true)
ifneq (,$(wildcard /usr/bin/yarn))
	CATALOGS_YARN = yarn
endif
endif

# Main targets

.PHONY: catalogs-fixtures
catalogs-fixtures:
	APP_ENV=dev $(PHP_RUN) bin/console akeneo:catalogs:fixtures

.PHONY: catalogs-tests
catalogs-tests:
	$(MAKE) catalogs-lint-back
	$(MAKE) catalogs-coupling-back
	$(MAKE) catalogs-coverage-back
	$(MAKE) catalogs-acceptance-back
	$(MAKE) catalogs-lint-front
	$(MAKE) catalogs-unit-front

.PHONY: catalogs-cs-fix
catalogs-cs-fix: catalogs-lint-front_fix catalogs-lint-back_fix

# TESTS BACK

.PHONY: catalogs-lint-back
catalogs-lint-back:
ifneq ($(CI),true)
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 -u www-data --rm php bin/console cache:warmup
endif
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run \
		--config=$(CATALOGS_PATH)/back/.php_cs.php
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--configuration $(CATALOGS_PATH)/back/phpstan.neon \
		--level=8 \
		$(CATALOGS_PATH)/back/src
	$(PHP_RUN) vendor/bin/psalm $(PSALM_ARGS) -c $(CATALOGS_PATH)/back/psalm.xml
	$(PHP_RUN) vendor/bin/phpinsights analyse $(PHPINSIGHTS_ARGS) --no-interaction \
		--config-path=$(CATALOGS_PATH)/back/phpinsights.php \
		$(CATALOGS_PATH)/back/src/
	$(PHP_RUN) bin/console lint:container

.PHONY: catalogs-coupling-back
catalogs-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect \
		--config-file=$(CATALOGS_PATH)/back/.php_cd.php \
		$(CATALOGS_PATH)/back/src

.PHONY: catalogs-unit-back
catalogs-unit-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--testsuite Catalogs_Unit \
		--log-junit var/tests/phpunit/phpunit_catalogs_unit.xml

.PHONY: catalogs-integration-back
catalogs-integration-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--testsuite Catalogs_Integration

.PHONY: catalogs-acceptance-back
catalogs-acceptance-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/behat --config $(CATALOGS_PATH)/back/behat.yml

.PHONY: catalogs-coverage-back
catalogs-coverage-back:
	XDEBUG_MODE=coverage APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--testsuite Catalogs_Unit \
		--log-junit var/tests/phpunit/phpunit_catalogs_unit.xml \
		--coverage-php coverage/Catalogs/Back/Global/unit.cov \
		--coverage-html coverage/Catalogs/Back/Unit/ \
		--coverage-filter $(CATALOGS_PATH)/back/src/
	XDEBUG_MODE=coverage APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--testsuite Catalogs_Integration \
		--log-junit var/tests/phpunit/phpunit_catalogs_integration.xml \
		--coverage-php coverage/Catalogs/Back/Global/integration.cov \
		--coverage-html coverage/Catalogs/Back/Integration/ \
		--coverage-filter $(CATALOGS_PATH)/back/src/

	# download phpcov binary
	$(DOCKER_COMPOSE) run -u www-data -w /srv/pim/var --rm php sh -c "test -e phpcov.phar || wget https://phar.phpunit.de/phpcov.phar && php phpcov.phar --version"
	# Merge code coverages
	$(PHP_RUN) -d memory_limit=-1 var/phpcov.phar merge \
		--html coverage/Catalogs/Back/Global/ \
		coverage/Catalogs/Back/Global/

# TESTS FRONT

.PHONY: catalogs-lint-front
catalogs-lint-front: yarn-policies
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs prettier --check
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs eslint
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs tsc --noEmit --strict

.PHONY: catalogs-unit-front
catalogs-unit-front: yarn-policies
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs jest --ci --coverage

# UTILS

.PHONY: catalogs-lint-front_fix
catalogs-lint-front_fix: yarn-policies
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs eslint --fix
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs prettier --write

.PHONY: catalogs-lint-back_fix
catalogs-lint-back_fix:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=$(CATALOGS_PATH)/back/.php_cs.php

# BINARIES

.PHONY: yarn-policies
yarn-policies: .yarn/releases/yarn-1.22.19.cjs

.yarn/releases/yarn-1.22.19.cjs:
ifneq ($(CI),true)
ifneq (,$(wildcard /usr/bin/yarn))
	yarn policies set-version 1.22.19
endif
endif
