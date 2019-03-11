##
## Target used run command related on Franklin insights bounded context
##

.PHONY: franklin-insights-coupling
franklin-insights-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/FranklinInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/FranklinInsights

.PHONY: franklin-insights-specs
franklin-insights-specs:
	$(PHP_EXEC) vendor/bin/phpspec run src/Akeneo/Pim/Automation/FranklinInsights/tests/back/Specification

.PHONY: franklin-insights-acceptance
franklin-insights-acceptance:
	$(PHP_EXEC) vendor/bin/behat -p acceptance -s franklin-insights

.PHONY: franklin-insights-integration
franklin-insights-integration:
	$(PHP_EXEC) vendor/bin/phpunit -c app --testsuite=Franklin_Insights

.PHONY: franklin-insights-end-to-end
franklin-insights-end-to-end:
	$(PHP_EXEC) vendor/bin/behat -p legacy -s franklin-insights

.PHONY: franklin-insights-tests
franklin-insights-tests: franklin-insights-specs franklin-insights-acceptance franklin-insights-integration franklin-insights-end-to-end
