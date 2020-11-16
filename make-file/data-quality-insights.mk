# Define here the targets your team needs to work and the targets to run tests on CI
#
# Which kind of tests do/should we have in the PIM?
# =================================================
# - lint (back and front)
# - unit (back and front)
# - acceptance (back and front)
# - integration (back and front)
# - end to end (API and UI)
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

.PHONY: data-quality-insights-integration-back
data-quality-insights-integration-back:
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit --testsuite=Data_Quality_Insights --testdox $(O)

.PHONY: data-quality-insights-unit-front
data-quality-insights-unit-front:
	$(YARN_RUN) jest --coverage=false --maxWorkers=4 --config src/Akeneo/Pim/Automation/DataQualityInsights/tests/front/unit/unit.jest.js ${W}

.PHONY: data-quality-insights-unit-front-watch
data-quality-insights-unit-front-watch:
	W="--watchAll" $(MAKE) data-quality-insights-unit-front

.PHONY: data-quality-insights-tests
data-quality-insights-tests: data-quality-insights-coupling-back data-quality-insights-lint-back data-quality-insights-phpstan data-quality-insights-unit-back data-quality-insights-unit-front data-quality-insights-integration-back
