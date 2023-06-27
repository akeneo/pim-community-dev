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

.PHONY: user-management-unit-back
user-management-unit-back: #Doc: launch PHPSpec for user-management bounded context
	$(PHP_RUN) vendor/bin/phpspec run tests/back/UserManagement/Specification
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/UserManagement/back/tests/Specification

.PHONY: user-management-coupling-back
user-management-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement

.PHONY: user-management-integration-back
user-management-integration-back: #Doc: launch PHPUnit integration tests for user-management bounded context
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit --testsuite PIM_Integration_Test --filter UserManagement $(O)

.PHONY: user-management-end-to-end-back
user-management-end-to-end-back: #Doc: launch PHPUnit end-to-end tests for user-management bounded context
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit --testsuite End_to_End --filter UserManagement $(O)

.PHONY: user-management-ci
user-management-ci: user-management-unit-back user-management-coupling-back user-management-integration-back user-management-end-to-end-back
