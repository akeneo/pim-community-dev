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

_APPS_YARN_RUN = $(YARN_RUN) run --cwd=src/Akeneo/Apps/front/

# Tests Back

apps-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Apps/back/tests/.php_cd.php src/Akeneo/Apps/back

apps-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Apps/back/tests/Unit/spec/

apps-acceptance-back: var/tests/behat/apps
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Apps/back/tests/Acceptance/behat.yml --format pim --out var/tests/behat/apps --format progress --out std --colors

apps-integration-back:
ifeq ($(CI),true)
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php Akeneo_Apps_Integration
else
	${PHP_RUN} vendor/bin/phpunit -c . --testsuite Akeneo_Apps_Integration $(O)
endif

apps-back:
	make apps-coupling-back
	make apps-unit-back
	make apps-acceptance-back
	make apps-integration-back

# Tests Front

apps-front-tests:
	$(_APPS_YARN_RUN) jest

apps-front-lint:
	$(_APPS_YARN_RUN) eslint
	$(_APPS_YARN_RUN) prettier --check

# Development

apps-front-tests-watch:
	$(_APPS_YARN_RUN) jest --watchAll --coverage

apps-front-lint-fix:
	$(_APPS_YARN_RUN) eslint --fix
	$(_APPS_YARN_RUN) prettier --write
