##
## Target used run command related on Franklin insights bounded context
##

.PHONY: franklin-insights-coupling
franklin-insights-coupling: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/FranklinInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/FranklinInsights

.PHONY: franklin-insights-phpstan
franklin-insights-phpstan: vendor
	$(PHP_EXEC) vendor/bin/phpstan analyse src/Akeneo/Pim/Automation/FranklinInsights -l 1

.PHONY: franklin-insights-specs
franklin-insights-specs: vendor
	$(PHP_EXEC) vendor/bin/phpspec run src/Akeneo/Pim/Automation/FranklinInsights/tests/back/Specification

.PHONY: franklin-insights-acceptance
franklin-insights-acceptance: vendor
	$(PHP_EXEC) vendor/bin/behat -p acceptance -s franklin-insights

.PHONY: franklin-insights-integration
franklin-insights-integration: vendor
	$(PHP_EXEC) vendor/bin/phpunit -c app --testsuite=Franklin_Insights

.PHONY: franklin-insights-end-to-end
franklin-insights-end-to-end: vendor
	$(PHP_EXEC) vendor/bin/behat -p legacy -s franklin-insights

.PHONY: franklin-insights-cs
franklin-insights-cs: vendor
	$(PHP_EXEC) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php
	$(YARN_EXEC) tslint -c src/Akeneo/Pim/Automation/FranklinInsights/tests/front/tslint.json src/Akeneo/Pim/Automation/FranklinInsights/**/*.ts

.PHONY: franklin-insights-tests
franklin-insights-tests: franklin-insights-coupling franklin-insights-specs franklin-insights-acceptance franklin-insights-integration franklin-insights-end-to-end franklin-insights-cs franklin-insights-phpstan
