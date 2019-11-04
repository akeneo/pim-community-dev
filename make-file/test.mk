var/tests/%:
	$(DOCKER_COMPOSE) run -u www-data --rm php mkdir -p $@

.PHONY: coupling-back
coupling-back: structure-coupling-back user-management-coupling-back channel-coupling-back enrichment-coupling-back apps-coupling-back

.PHONY: check-pullup
check-pullup:
	${PHP_RUN} bin/check-pullup

### Lint tests
.PHONY: lint-back
lint-back: var/cache/dev
	${PHP_RUN} vendor/bin/phpstan analyse src/Akeneo/Pim -l 1
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php

.PHONY: lint-front
lint-front:
	$(YARN_RUN) lint

### Unit tests
.PHONY: unit-back
unit-back: var/tests/phpspec
ifeq ($(CI),1)
	${PHP_RUN} vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml
	.circleci/find_non_executed_phpspec.sh
else
	${PHP_RUN} vendor/bin/phpspec run $(O)
endif

.PHONY: unit-front
unit-front:
	$(YARN_RUN) unit

### Acceptance tests
.PHONY: acceptance-back
acceptance-back: apps-acceptance-back
	${PHP_RUN} vendor/bin/behat -p acceptance --format pim --out var/tests/behat --format progress --out std --colors

.PHONY: acceptance-front
acceptance-front:
	MAX_RANDOM_LATENCY_MS=100 $(YARN_RUN) acceptance run acceptance ./tests/features

### Integration tests
.PHONY: integration-front
integration-front:
	$(YARN_RUN) integration

.PHONY: integration-back
integration-back: var/tests/phpunit apps-integration-back
ifeq ($(CI),1)
	.circleci/run_phpunit.sh . PIM_Integration_Test
else
	${PHP_RUN} vendor/bin/phpunit -c . --testsuite PIM_Integration_Test $(O)
endif

### Integration tests
.PHONY: end-to-end-back
end-to-end-back: var/tests/phpunit
ifeq ($(CI),1)
	.circleci/run_phpunit.sh . End_to_End
else
	${PHP_RUN} vendor/bin/phpunit -c . --testsuite End_to_End $(O)
endif
