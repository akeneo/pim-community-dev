##
## Target used run command related on Franklin insights bounded context
##

.PHONY: franklin-insights-coupling-back
franklin-insights-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/FranklinInsights/tests/back/.php_cd.php src/Akeneo/Pim/Automation/FranklinInsights

.PHONY: franklin-insights-lint-back
franklin-insights-lint-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php

.PHONY: franklin-insights-lint-front
franklin-insights-lint-front:
	$(YARN_RUN) tslint -c src/Akeneo/Pim/Automation/FranklinInsights/tslint.json src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx}
	$(NODE_RUN) ./node_modules/.bin/prettier --config src/Akeneo/Pim/Automation/FranklinInsights/.prettierrc.json --check src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx}
	$(YARN_RUN) lint

.PHONY: franklin-insights-phpstan
franklin-insights-phpstan: var/cache/dev
	$(PHP_EXEC) vendor/bin/phpstan analyse src/Akeneo/Pim/Automation/FranklinInsights -l 1

.PHONY: franklin-insights-unit
franklin-insights-unit:
	$(PHP_EXEC) vendor/bin/phpspec run src/Akeneo/Pim/Automation/FranklinInsights/tests/back/Specification

.PHONY: franklin-insights-unit-front
franklin-insights-unit-front:
	$(YARN_EXEC) jest --maxWorkers=4 --config src/Akeneo/Pim/Automation/FranklinInsights/tests/front/unit/unit.jest.js ${W}

.PHONY: franklin-insights-unit-front-watch
franklin-insights-unit-front-watch:
	W="--watchAll" $(MAKE) franklin-insights-unit-front

.PHONY: franklin-insights-acceptance
franklin-insights-acceptance:
	$(PHP_EXEC) vendor/bin/behat -p acceptance -s franklin-insights

.PHONY: franklin-insights-integration
franklin-insights-integration:
	$(PHP_EXEC) vendor/bin/phpunit --testsuite=Franklin_Insights --testdox

.PHONY: franklin-insights-end-to-end
franklin-insights-end-to-end:
	$(PHP_EXEC) vendor/bin/behat -p legacy -s insights

.PHONY: franklin-insights-cs-fix
franklin-insights-cs-fix:
	$(PHP_EXEC) vendor/bin/php-cs-fixer fix --config=.php_cs.php
	$(YARN_RUN) tslint -c src/Akeneo/Pim/Automation/FranklinInsights/tslint.json src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx} --fix
	$(DOCKER_COMPOSE) run --rm node ./node_modules/.bin/prettier --config src/Akeneo/Pim/Automation/FranklinInsights/.prettierrc.json --check src/Akeneo/Pim/Automation/FranklinInsights/**/*.{ts,tsx} --write
	$(YARN_EXEC) lint-fix

.PHONY: franklin-insights-tests
franklin-insights-tests: franklin-insights-coupling-back franklin-insights-lint-back franklin-insights-lint-front franklin-insights-unit franklin-insights-unit-front franklin-insights-acceptance franklin-insights-integration franklin-insights-end-to-end
