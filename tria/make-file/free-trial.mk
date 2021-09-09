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

DATABASE_CATALOG_FREE_TRIAL ?= src/Akeneo/FreeTrial/back/Infrastructure/Symfony/Resources/fixtures/free_trial_catalog

.PHONY: trial-dev
trial-dev: #Doc: run docker-compose up, clean symfony cache, run webpack dev & install free_trial_catalog database in dev environment
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	APP_ENV=dev $(MAKE) assets
	$(MAKE) css
	$(MAKE) front-packages
	$(MAKE) javascript-dev
	cd $(PIM_SRC_PATH) && docker/wait_docker_up.sh
	APP_ENV=dev O="--catalog $(DATABASE_CATALOG_FREE_TRIAL)" $(MAKE) database


.PHONY: free-trial-lint-back
free-trial-lint-back: #Doc: launch PHPStan and php-cs-fixer for the free trial
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/FreeTrial/back
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration=src/Akeneo/FreeTrial/tests/back/phpstan.neon.dist

.PHONY: free-trial-cs-fix-back
free-trial-cs-fix-back: #Doc: fix CS back for the free trial
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=.php_cs.php src/Akeneo/FreeTrial/back

.PHONY: free-trial-unit-back
free-trial-unit-back: #Doc: launch PHPSec for the free trial
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/FreeTrial/tests/back/Specification

.PHONY: free-trial-coupling-back
free-trial-coupling-back: #Doc: launch coupling detector for the free trial
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/FreeTrial/tests/back/.php_cd.php src/Akeneo/FreeTrial/back

.PHONY: free-trial-integration-back
free-trial-integration-back: #Doc: launch PHPUnit integration test for the free trial
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit --testsuite=Free_Trial --testdox $(O)

.PHONY: free-trial-tests
free-trial-tests: free-trial-lint-back free-trial-coupling-back free-trial-unit-back free-trial-integration-back
