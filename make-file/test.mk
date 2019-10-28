.PHONY: coupling
coupling: structure-coupling user-management-coupling channel-coupling enrichment-coupling

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
unit-back:
	${PHP_RUN} vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml
	./.circleci/find_non_executed_phpspec.sh

.PHONY: unit-front
unit-front:
	$(YARN_RUN) unit

### Acceptance tests
.PHONY: acceptance-back
acceptance-back:
	${PHP_RUN} vendor/bin/behat --strict -p acceptance -vv

.PHONY: acceptance-front
acceptance-front:
	MAX_RANDOM_LATENCY_MS=100 $(YARN_RUN) acceptance run acceptance ./tests/features

### Integration tests
.PHONY: integration-front
integration-front:
	$(YARN_RUN) integration

