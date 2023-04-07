CATALOGS_PATH ?= components/catalogs
CATALOGS_BOOTSTRAP_PATH ?= /srv/pim/config/bootstrap.php
CATALOGS_YARN = $(YARN_RUN)

ifeq ($(CI),true)
	BEHAT_ARGS += --no-snippets --no-interaction
	RECTOR_ARGS += --no-progress-bar
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
	$(MAKE) catalogs-cs-back
	$(MAKE) catalogs-lint-back
	$(MAKE) catalogs-check-configuration-back
	$(MAKE) catalogs-coupling-back
	$(MAKE) catalogs-coverage-back
	$(MAKE) catalogs-mutation-unit-back
	$(MAKE) catalogs-acceptance-back
	$(MAKE) catalogs-lint-front
	$(MAKE) catalogs-unit-front
	$(MAKE) catalogs-integration-front

.PHONY: catalogs-cs-fix
catalogs-cs-fix: catalogs-lint-front_fix catalogs-lint-back_fix

# TESTS BACK

.PHONY: catalogs-cs-back
catalogs-cs-back:
	$(PHP_RUN) vendor/bin/rector process $(RECTOR_ARGS) --dry-run \
		--config=$(CATALOGS_PATH)/back/rector.php
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run \
		--config=$(CATALOGS_PATH)/back/.php_cs.php

.PHONY: catalogs-lint-back
catalogs-lint-back:
ifneq ($(CI),true)
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 -u www-data --rm php bin/console cache:warmup
endif
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--configuration $(CATALOGS_PATH)/back/phpstan.neon \
		--level=8 \
		$(CATALOGS_PATH)/back/src
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--configuration $(CATALOGS_PATH)/back/phpstan-deprecations.neon \
		--level=8 \
		$(CATALOGS_PATH)/back/src
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--configuration $(CATALOGS_PATH)/back/phpstan-deprecations.neon\
		--level=5 \
		$(CATALOGS_PATH)/back/tests
	$(PHP_RUN) vendor/bin/psalm $(PSALM_ARGS) -c $(CATALOGS_PATH)/back/psalm.xml

.PHONY: catalogs-check-configuration-back
catalogs-check-configuration-back:
ifneq ($(CI),true)
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 -u www-data --rm php bin/console cache:warmup
endif
	$(PHP_RUN) bin/console lint:container

.PHONY: catalogs-coupling-back
catalogs-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect \
		--config-file=$(CATALOGS_PATH)/back/.php_cd.php \
		$(CATALOGS_PATH)/back/src
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements \
		--config-file=$(CATALOGS_PATH)/back/.php_cd.php \
		$(CATALOGS_PATH)/back/src

.PHONY: catalogs-unit-back
catalogs-unit-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--testsuite Catalogs_Unit \
		--order-by random \
		--log-junit var/tests/phpunit/phpunit_catalogs_unit.xml

.PHONY: catalogs-integration-back
catalogs-integration-back:
	APP_ENV=test EXPERIMENTAL_TEST_DATABASE=1 $(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--testsuite Catalogs_Integration \
		--random-order-seed 1680510512 \
		--testdox \
		--verbose \
		--log-junit var/tests/phpunit/phpunit_catalogs_integration.xml

.PHONY: catalogs-acceptance-back
catalogs-acceptance-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/behat --config $(CATALOGS_PATH)/back/behat.yml

.PHONY: catalogs-performance-back
catalogs-performance-back:
	APP_ENV=test EXPERIMENTAL_TEST_DATABASE=1 \
	$(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--testsuite Catalogs_Performance \
		--order-by random \
		--log-junit var/tests/phpunit/phpunit_catalogs_performance.xml

.PHONY: catalogs-coverage-back
catalogs-coverage-back:
	XDEBUG_MODE=coverage APP_ENV=test EXPERIMENTAL_TEST_DATABASE=1 \
	$(PHP_RUN) vendor/bin/phpunit \
		--configuration $(CATALOGS_PATH)/back/ \
		--bootstrap $(CATALOGS_BOOTSTRAP_PATH) \
		--log-junit var/tests/phpunit/phpunit_catalogs_global.xml \
		--coverage-html coverage/Catalogs/Back/Global/ \
		--coverage-filter $(CATALOGS_PATH)/back/src/ \
		--testsuite Catalogs_Unit,Catalogs_Integration

.PHONY: catalogs-mutation-back
catalogs-mutation-back: var/infection.phar
	XDEBUG_MODE=coverage APP_ENV=test $(PHP_RUN) var/infection.phar \
		--configuration=$(CATALOGS_PATH)/back/infection.json \
		--only-covered \
		--show-mutations

.PHONY: catalogs-mutation-unit-back
catalogs-mutation-unit-back: var/infection.phar
	XDEBUG_MODE=coverage APP_ENV=test $(PHP_RUN) var/infection.phar \
		--configuration=$(CATALOGS_PATH)/back/infection.json \
		--only-covered \
		--show-mutations \
		--test-framework-options="--testsuite=Catalogs_Unit" \
		--min-covered-msi=100

.PHONY: catalogs-mutation-integration-back
catalogs-mutation-integration-back: var/infection.phar
	XDEBUG_MODE=coverage APP_ENV=test $(PHP_RUN) var/infection.phar \
		--configuration=$(CATALOGS_PATH)/back/infection.json \
		--only-covered \
		--show-mutations \
		--test-framework-options="--testsuite=Catalogs_Integration"

# TESTS FRONT

.PHONY: catalogs-lint-front
catalogs-lint-front: yarn-policies
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs prettier --check
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs eslint
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs tsc --noEmit --strict
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs depcruise

.PHONY: catalogs-unit-front
catalogs-unit-front: yarn-policies
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs jest:unit --ci --coverage

.PHONY: catalogs-integration-front
catalogs-integration-front: yarn-policies
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs jest:integration --ci --runInBand

# UTILS

.PHONY: catalogs-lint-front_fix
catalogs-lint-front_fix: yarn-policies
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs eslint --fix
	$(CATALOGS_YARN) workspace @akeneo-pim-community/catalogs prettier --write

.PHONY: catalogs-lint-back_fix
catalogs-lint-back_fix:
	$(PHP_RUN) vendor/bin/rector process --config=$(CATALOGS_PATH)/back/rector.php
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

var/infection.phar:
	wget -q https://github.com/infection/infection/releases/download/0.26.0/infection.phar -O var/infection.phar
	wget -q https://github.com/infection/infection/releases/download/0.26.0/infection.phar.asc -O var/infection.phar.asc
	echo "80b26af31585cf98f6a898c34aa961c0bd4289ad135fa6771831bcd1d47aa5cc  var/infection.phar" | sha256sum --check
	echo "5052975aaf68cc4010b9726bd00ac2ec3c0cf0123f94044ae37aa3d892cf02c8  var/infection.phar.asc" | sha256sum --check
	chmod +x var/infection.phar
