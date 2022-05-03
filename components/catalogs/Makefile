CATALOGS_PATH ?= components/catalogs

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
	$(PHP_RUN) vendor/bin/psalm -c $(CATALOGS_PATH)/back/psalm.xml
	$(PHP_RUN) vendor/bin/phpinsights analyse --summary --no-interaction \
		--config-path=$(CATALOGS_PATH)/back/phpinsights.php \
		$(CATALOGS_PATH)/back/src/

.PHONY: catalogs-coupling-back
catalogs-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect \
		--config-file=$(CATALOGS_PATH)/back/.php_cd.php \
		$(CATALOGS_PATH)/back/src

.PHONY: catalogs-unit-back
catalogs-unit-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . \
		--testsuite Catalogs_Unit \
		--log-junit var/tests/phpunit/phpunit_catalogs_unit.xml

.PHONY: catalogs-integration-back
catalogs-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . \
		--testsuite Catalogs_Integration \
		--log-junit var/tests/phpunit/phpunit_catalogs_integration.xml

# TESTS FRONT

.PHONY: catalogs-lint-front
catalogs-lint-front:
	cat <<- EOF | $(NODE_RUN) sh
		yarn run --cwd=$(CATALOGS_PATH)/front/product-selection-builder/ prettier --check
		yarn run --cwd=$(CATALOGS_PATH)/front/product-selection-builder/ eslint
		yarn run --cwd=$(CATALOGS_PATH)/front/product-selection-builder/ tsc --noEmit --strict
	@<<- EOF

.PHONY: catalogs-unit-front
catalogs-unit-front:
	cat <<- EOF | $(NODE_RUN) sh
		yarn run --cwd=$(CATALOGS_PATH)/front/product-selection-builder/ jest --clearCache
		yarn run --cwd=$(CATALOGS_PATH)/front/product-selection-builder/ jest --ci --coverage
	@<<- EOF

# UTILS

.PHONY: catalogs-lint-front_fix
catalogs-lint-front_fix:
	cat <<- EOF | $(NODE_RUN) sh
		yarn run --cwd=$(CATALOGS_PATH)/front/product-selection-builder/ eslint --fix
		yarn run --cwd=$(CATALOGS_PATH)/front/product-selection-builder/ prettier --write
	@<<- EOF
