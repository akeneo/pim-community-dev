##
## Target used run command related on Data Quality Insights bounded context
##

.PHONY: data-quality-insights-coupling-back
data-quality-insights-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/DataQualityInsights

.PHONY: data-quality-insights-phpstan
data-quality-insights-phpstan: var/cache/dev
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/phpstan.neon.dist

.PHONY: data-quality-insights-unit-back
data-quality-insights-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/Specification

.PHONY: data-quality-insights-lint-back
data-quality-insights-lint-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/Pim/Automation/DataQualityInsights/back

.PHONY: data-quality-insights-cs-fix
data-quality-insights-cs-fix:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php src/Akeneo/Pim/Automation/DataQualityInsights/back

.PHONY: data-quality-insights-tests
data-quality-insights-tests: data-quality-insights-coupling-back data-quality-insights-lint-back data-quality-insights-phpstan data-quality-insights-unit-back
