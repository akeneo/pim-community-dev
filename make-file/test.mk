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
	${PHP_RUN} vendor/bin/phpspec run
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
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php PIM_Integration_Test
else
	@echo Run integration test locally is too long, please use the target define for your bounded context (ex: bounded-context-integration-back)
endif

### End to end tests
.PHONY: end-to-end-back
end-to-end-back: var/tests/phpunit
ifeq ($(CI),1)
	.circleci/run_phpunit.sh . PIM_Integration_Test
else
	@echo Run end to end test locally is too long, please use the target define for your bounded context (ex: bounded-context-end-to-end-back)
endif

# How to debug a behat locally?
# -----------------------------
#
# Run the following command:
# make end-to-end-legacy O=my/feature/file.feature:23
#
# Don't forget to pass *O*ption to avoid to run the whole suite.
# Please add dependencies to this tagert and let it die

.PHONY: end-to-end-legacy
end-to-end-legacy: var/tests/behat
ifeq ($(CI),1)
	.circleci/run_behat.sh $(SUITE)
	.circleci/run_behat.sh critical
else
	${PHP_RUN} vendor/bin/behat -p legacy -s all ${0}
endif

