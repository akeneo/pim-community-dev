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

_CONNECTIVITY_CONNECTION_YARN_RUN = $(YARN_RUN) run --cwd=src/Akeneo/Connectivity/Connection/front/

# Tests Back

connectivity-connection-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Connectivity/Connection/back/tests/.php_cd.php src/Akeneo/Connectivity/Connection/back

connectivity-connection-static-analysis-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Connectivity/Connection/back/tests/phpstan.neon
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Connectivity/Connection/back/tests/phpstan-infra.neon

connectivity-connection-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Connectivity/Connection/back/tests/Unit/spec/

connectivity-connection-acceptance-back: var/tests/behat/connectivity/connection
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Connectivity/Connection/back/tests/Acceptance/behat.yml --format pim --out var/tests/behat/connectivity/connection --format progress --out std --colors

connectivity-connection-integration-back:
ifeq ($(CI),true)
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php Akeneo_Connectivity_Connection_Integration
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_Integration $(0)
endif

connectivity-connection-e2e-back:
ifeq ($(CI),true)
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php Akeneo_Connectivity_Connection_EndToEnd
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_EndToEnd $(0)
endif

connectivity-connection-back:
	make connectivity-connection-coupling-back
	make connectivity-connection-static-analysis-back
	make connectivity-connection-unit-back
	make connectivity-connection-acceptance-back
	make connectivity-connection-integration-back
	make connectivity-connection-e2e-back

# Tests Front

connectivity-connection-unit-front:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) jest --ci

connectivity-connection-lint-front:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) eslint
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) prettier --check

# Development

connectivity-connection-unit-front_coverage:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) jest --coverage

connectivity-connection-unit-front_watch:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) jest --watchAll

connectivity-connection-lint-front_fix:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) eslint --fix
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) prettier --write
