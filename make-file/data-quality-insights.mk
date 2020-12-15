##
## Target used run command related on Data Quality Insights bounded context
##

include test.mk

.PHONY: data-quality-insights-coupling-back
data-quality-insights-coupling-back: #Doc: launch coupling detector for quality-insights
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/DataQualityInsights

.PHONY: data-quality-insights-phpstan
data-quality-insights-phpstan: var/cache/dev #Doc: launch PHPStan for quality-insights
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/phpstan.neon.dist

.PHONY: data-quality-insights-unit-back
data-quality-insights-unit-back: #Doc: launch PHPSec unit tests for quality-insights
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Pim/Automation/DataQualityInsights/tests/back/Specification

.PHONY: data-quality-insights-lint-back
data-quality-insights-lint-back: #Doc: launch PHP linter for quality-insights
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/Pim/Automation/DataQualityInsights/back
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php vendor/akeneo/pim-community-dev/src/Akeneo/Pim/Automation/DataQualityInsights/back

.PHONY: data-quality-insights-lint-front
data-quality-insights-lint-front:
	$(YARN_RUN) prettier --config vendor/akeneo/pim-community-dev/.prettierrc.json --parser typescript --write "./src/Akeneo/Pim/Automation/DataQualityInsights/front/**/*.{js,ts,tsx}"

.PHONY: data-quality-insights-cs-fix
data-quality-insights-cs-fix: #Doc: launch PHP fixer for quality-insights
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php src/Akeneo/Pim/Automation/DataQualityInsights/back

.PHONY: data-quality-insights-integration-back
data-quality-insights-integration-back: #Doc: launch PHPunit integration tests for quality-insights
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit --testsuite=Data_Quality_Insights --testdox

.PHONY: data-quality-insights-unit-front
data-quality-insights-unit-front: #Doc: launch JS unit test for quality insights
	$(YARN_RUN) jest --coverage=false --maxWorkers=4 --config src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/unit.jest.js ${W}

.PHONY: data-quality-insights-unit-front-watch
data-quality-insights-unit-front-watch: #Doc: launch and watch JS unit test for quality insights
	W="--watchAll" $(MAKE) data-quality-insights-unit-front

.PHONY: data-quality-insights-tests
data-quality-insights-tests: #Doc: launch all tests for quality insights
data-quality-insights-tests: data-quality-insights-coupling-back data-quality-insights-lint-back data-quality-insights-phpstan data-quality-insights-unit-back data-quality-insights-unit-front data-quality-insights-integration-back
