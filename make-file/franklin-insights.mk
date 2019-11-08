# Define here the targets your team needs to work and the targets to run tests on CI
#
# Which kind of tests do/should we have in the PIM?
# =================================================
# - lint (back and front)
# - unit (back and front)
# - acceptance (back and front)
# - integration (back and front)
# - end to end
#
# You should at least define a target for every kind of tests we previously listed with the following pattern:
#
# bounded-context-(lint|unit|acceptance|integration|end-to-end)-(back|front)
#
# Examples: asset-unit-back, asset-acceptance-front, asset-integration-back.
#
# How to define targets with a specfic configuration for the CI?
# ==============================================================
# For instance phpspec does not support multiple formats that means you can run the same command on the CI and locally.
# If you need to do that you can use an environment variable named CI which is a "boolean" (don't forget, env vars are strings).
# If its value equals 1 run the command configured for the CI otherwise configure it to run it locally.
#
# Example:
# -------
# target-name:
# ifeq ($(CI),true)
#	execute a command on the CI
# else
#	execute a command locally
# endif
#
# How can I can run test tools with specfic configuration?
# ========================================================
# You can define an environement variable to make targets configurable. Let's name O for *O*ption but it does not matter.
#
# Example:
# --------
# bounded-context-unit-back: var/tests/phpspec
#	 ${PHP_RUN} vendor/bin/phpspec run $(O)
#
# Run a spec
# ----------
#
# make bounded-context-unit-back O=my/spec.php
#
# How to run them on the CI?
# ==========================
# You should add them as dependency of the "main targets" which in `make-file/test.mk`.
# You should have a look to `coupling-back` this target does not run a command but only depends on other ones.
#
# /!\ CAUTION /!\ By default some "main targets" run commands because of our legacy code (hard to reconfigure all tools).
# Make sure the tests run by the targets defined here does not run by the main targets too
#

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

.PHONY: franklin-insights-integration-back
franklin-insights-integration-back:
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Franklin_Insights
else
	${PHP_RUN} vendor/bin/phpunit -c . --testsuite Franklin_Insights --testdox $(O)
endif

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
franklin-insights-tests: franklin-insights-coupling-back franklin-insights-lint-back franklin-insights-lint-front franklin-insights-unit franklin-insights-unit-front franklin-insights-acceptance franklin-insights-integration-back franklin-insights-end-to-end
