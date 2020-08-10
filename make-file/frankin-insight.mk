##
## Target used run command related on Franklin insights bounded context
##

.PHONY: franklin-insights-coupling
franklin-insights-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/FranklinInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/FranklinInsights

.PHONY: franklin-insights-specs
franklin-insights-specs:
	$(PHP_EXEC) vendor/bin/phpspec run src/Akeneo/Pim/Automation/FranklinInsights/tests/back/Specification

.PHONY: franklin-insights-tests
franklin-insights-tests: franklin-insights-specs
