var/tests/%: #Doc: run the selected test
	$(DOCKER_COMPOSE) run -u www-data --rm php mkdir -p $@

.PHONY: find-legacy-translations
find-legacy-translations: #Doc: run find_legacy_translations.sh script
	vendor/akeneo/pim-community-dev/.circleci/find_legacy_translations.sh

### Lint tests
.PHONY: lint-back
lint-back: #Doc: launch all PHP linter tests
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php

### Unit tests
.PHONY: unit-back
unit-back: $(PIM_SRC_PATH)/var/tests/phpspec community-unit-back #Doc: launch all PHPSec unit tests
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T -u www-data --rm php php vendor/bin/phpspec run --format=junit > $(PIM_SRC_PATH)/var/tests/phpspec/specs.xml
	cd $(PIM_SRC_PATH) && vendor/akeneo/pim-community-dev/.circleci/find_non_executed_phpspec.sh
else
	${PHP_RUN} vendor/bin/phpspec run
endif

.PHONY: community-unit-back
community-unit-back: $(PIM_SRC_PATH)/var/tests/phpspec #Doc: launch PHPSpec for PIM CE dev
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T -u www-data --rm php sh -c "cd vendor/akeneo/pim-community-dev && php ../../../vendor/bin/phpspec run  --format=junit > ../../../var/tests/phpspec/specs-ce.xml"
else
	$(DOCKER_COMPOSE) run -u www-data --rm php sh -c "cd vendor/akeneo/pim-community-dev && php ../../../vendor/bin/phpspec run"
endif

.PHONY: unit-front
unit-front: #Doc: launch all JS unit tests
	$(YARN_RUN) unit

.PHONY: acceptance-front
acceptance-front:
	MAX_RANDOM_LATENCY_MS=100 $(YARN_RUN) acceptance run acceptance ./tests/features #Doc: launch YARN acceptance

.PHONY: integration-back
integration-back: $(PIM_SRC_PATH)/var/tests/phpunit pim-integration-back #Doc: launch all integration back tests

.PHONY: pim-integration-back
pim-integration-back: #Doc: launch all PHPUnit integration tests
ifeq ($(CI),true)
	cd $(PIM_SRC_PATH) && vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php PIM_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit --testsuite PIM_Integration_Test
endif

### Migration tests
.PHONY: migration-back
migration-back: $(PIM_SRC_PATH)/var/tests/phpunit #Doc: launch PHP unit tests for migration
	cp vendor/akeneo/pim-community-dev/upgrades/schema/*.php upgrades/schema/.
ifeq ($(CI),true)
	cd $(PIM_SRC_PATH) && vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php PIM_Migration_Test
else
	@echo Migration files from CE copied into upgrade/ dir. Do not commit them.
	APP_ENV=test $(PHP_RUN) ./vendor/bin/phpunit -c . --testsuite PIM_Migration_Test
endif

### End to end tests
.PHONY: end-to-end-back
end-to-end-back: $(PIM_SRC_PATH)/var/tests/phpunit #Doc: launch PHP unit end to end tests
ifeq ($(CI),true)
	cd $(PIM_SRC_PATH) && vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php End_to_End
else
	APP_ENV=test $(DOCKER_COMPOSE) run -u www-data --rm php vendor/bin/phpunit --testsuite End_to_End
endif
