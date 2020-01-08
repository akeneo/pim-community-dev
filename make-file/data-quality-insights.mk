##
## Target used run command related on Data Quality Insights bounded context
##

.PHONY: data-quality-insights-coupling-back
data-quality-insights-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/DataQualityInsights/back/tests/.php_cd.php src/Akeneo/Pim/Automation/DataQualityInsights

.PHONY: data-quality-insights-phpstan
data-quality-insights-phpstan: var/cache/dev
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/Automation/DataQualityInsights/back/tests/phpstan.neon.dist

.PHONY: data-quality-insights-unit-back
data-quality-insights-unit-back:
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T -u www-data --rm php php vendor/bin/phpspec run src/Akeneo/Pim/Automation/DataQualityInsights/back/tests/Specification --format=junit > var/tests/phpspec/data-quality-insights.xml
else
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Pim/Automation/DataQualityInsights/back/tests/Specification
endif

.PHONY: data-quality-insights-lint-back
data-quality-insights-lint-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/Pim/Automation/DataQualityInsights/back

.PHONY: data-quality-insights-cs-fix
data-quality-insights-cs-fix:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php src/Akeneo/Pim/Automation/DataQualityInsights/back

.PHONY: data-quality-insights-integration-back
data-quality-insights-integration-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit --testsuite=Data_Quality_Insights --testdox

.PHONY: data-quality-insights-tests
data-quality-insights-tests: data-quality-insights-coupling-back data-quality-insights-lint-back data-quality-insights-phpstan data-quality-insights-unit-back data-quality-insights-integration-back
