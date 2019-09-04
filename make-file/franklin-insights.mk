##
## Target used run command related on Franklin insights bounded context
##

.PHONY: franklin-insights-coupling
franklin-insights-coupling:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/FranklinInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/FranklinInsights

.PHONY: franklin-insights-phpstan
franklin-insights-phpstan:
	$(PHP_EXEC) vendor/bin/phpstan analyse src/Akeneo/Pim/Automation/FranklinInsights -l 1

.PHONY: franklin-insights-unit
franklin-insights-unit:
	$(PHP_EXEC) vendor/bin/phpspec run src/Akeneo/Pim/Automation/FranklinInsights/tests/back/Specification

.PHONY: franklin-insights-acceptance
franklin-insights-acceptance:
	$(PHP_EXEC) vendor/bin/behat -p acceptance -s franklin-insights

.PHONY: franklin-insights-integration
franklin-insights-integration:
	$(PHP_EXEC) vendor/bin/phpunit -c app --testsuite=Franklin_Insights --testdox

.PHONY: franklin-insights-end-to-end
franklin-insights-end-to-end:
	$(PHP_EXEC) vendor/bin/behat -p legacy -s insights

.PHONY: franklin-insights-cs
franklin-insights-cs:
	$(PHP_EXEC) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php
	$(YARN_EXEC) tslint -c src/Akeneo/Pim/Automation/FranklinInsights/tslint.json src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx}
	$(DOCKER_COMPOSE) run --rm node ./node_modules/.bin/prettier --config src/Akeneo/Pim/Automation/FranklinInsights/.prettierrc.json --check src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx}

.PHONY: franklin-insights-cs-fix
franklin-insights-cs-fix:
	$(PHP_EXEC) vendor/bin/php-cs-fixer fix --config=.php_cs.php
	$(YARN_EXEC) tslint -c src/Akeneo/Pim/Automation/FranklinInsights/tslint.json src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx} --fix
	$(DOCKER_COMPOSE) run --rm node ./node_modules/.bin/prettier --config src/Akeneo/Pim/Automation/FranklinInsights/.prettierrc.json --check src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx} --write

.PHONY: franklin-insights-tests
franklin-insights-tests: franklin-insights-coupling franklin-insights-cs franklin-insights-phpstan franklin-insights-unit franklin-insights-acceptance franklin-insights-integration franklin-insights-end-to-end
