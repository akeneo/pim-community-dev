include make-file/dev.mk
include make-file/test.mk

.PHONY: performance-analytics-unit-back
performance-analytics-unit-back: #Doc: launch PHPSpec for performance analytics
	$(DOCKER_COMPOSE) run --rm php sh -c "php vendor/bin/phpspec run --config=components/performance-analytics/back/tests/phpspec.yml.dist $(O)"

.PHONY: performance-analytics-lint-back
performance-analytics-lint-back:
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/performance-analytics/back/tests/.php_cs.php components/performance-analytics/back
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/performance-analytics/back/tests/phpstan.neon

.PHONY: performance-analytics-lint-back-fix
performance-analytics-lint-back-fix: #Doc: launch php-cs-fixer without dry-run for performance analytic
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/performance-analytics/back/tests/.php_cs.php components/performance-analytics/back

.PHONY: performance-analytics-coupling-back
performance-analytics-coupling-back: #Doc: launch coupling detector for performance analytics
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/performance-analytics/back/tests/.php_cd.php components/performance-analytics/back/src

.PHONY: performance-analytics-integration-back
performance-analytics-integration-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c components/performance-analytics/back/tests/phpunit.xml --testsuite Integration_Test $(O)

.PHONY: performance-analytics-lint-front
performance-analytics-lint-front: #Doc: launch Lint check and tsc for performance analytics
	$(YARN_RUN) workspace @akeneo-pim-enterprise/performance-analytics lint:check
	$(YARN_RUN) workspace @akeneo-pim-enterprise/performance-analytics tsc --noEmit --strict --incremental false

.PHONY: performance-analytics-lint-front-fix
performance-analytics-lint-front-fix:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/performance-analytics lint:fix

.PHONY: performance-analytics-unit-front
performance-analytics-unit-front: #Doc: launch unit tests for performance analytics Front
	$(YARN_RUN) workspace @akeneo-pim-enterprise/performance-analytics test:unit:run

.PHONY: performance-analytics-test-back
performance-analytics-test-back: performance-analytics-lint-back performance-analytics-unit-back performance-analytics-coupling-back performance-analytics-integration-back

.PHONY: performance-analytics-test-front
performance-analytics-test-front: performance-analytics-lint-front performance-analytics-unit-front

.PHONY: performance-analytics-test
performance-analytics-test: performance-analytics-test-back performance-analytics-test-front
