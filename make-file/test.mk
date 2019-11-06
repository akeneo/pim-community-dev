var/tests/%:
	$(DOCKER_COMPOSE) run -u www-data --rm php mkdir -p $@

.PHONY: coupling-back
coupling-back: twa-coupling-back asset-coupling-back franklin-insights-coupling-back reference-entity-coupling-back asset-manager-coupling-back rule-engine-coupling-back workflow-coupling-back permission-coupling-back

.PHONY: check-pullup
check-pullup:
	${PHP_RUN} vendor/akeneo/pim-community-dev/bin/check-pullup

### Lint tests
.PHONY: lint-back
lint-back: var/cache/dev reference-entity-lint-back asset-manager-lint-back
	${PHP_RUN} vendor/bin/phpstan analyse src/Akeneo/Pim -l 1
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php

.PHONY: lint-front
lint-front: franklin-insights-lint-front
	$(YARN_RUN) lint

### Unit tests
.PHONY: unit-back
unit-back: var/tests/phpspec asset-manager-unit-back reference-entity-unit-back
ifeq ($(CI),1)
	${PHP_RUN} vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml
	vendor/akeneo/pim-community-dev/.circleci/find_non_executed_phpspec.sh
else
	${PHP_RUN} vendor/bin/phpspec run $(O)
endif

.PHONY: unit-front
unit-front:
	$(YARN_RUN) unit

### Acceptance tests
.PHONY: acceptance-back
acceptance-back: var/tests/behat reference-entity-acceptance-back asset-manager-acceptance-back
	${PHP_RUN} vendor/bin/behat -p acceptance --format pim --out var/tests/behat --format progress --out std --colors

.PHONY: acceptance-front
acceptance-front: asset-manager-acceptance-front
	MAX_RANDOM_LATENCY_MS=100 $(YARN_RUN) acceptance run acceptance ./tests/features

### Integration tests
.PHONY: integration-front
integration-front:
	$(YARN_RUN) integration

.PHONY: integration-back
integration-back: var/tests/phpunit franklin-insights-integration-back asset-manager-integration-back reference-entity-integration-back
ifeq ($(CI),1)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php PIM_Integration_Test
else
	${PHP_RUN} vendor/bin/phpunit -c . --testsuite PIM_Integration_Test $(O)
endif

### End to end tests
.PHONY: end-to-end-back
end-to-end-back: var/tests/phpunit
ifeq ($(CI),1)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php End_to_End
else
	${PHP_RUN} vendor/bin/phpunit -c . --testsuite End_to_End $(O)
endif
