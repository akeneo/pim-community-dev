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
_PERMISSION_FORM_YARN_RUN = $(YARN_RUN) run --cwd=src/Akeneo/Connectivity/Connection/workspaces/permission-form/

# Tests Back

connectivity-connection-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Connectivity/Connection/back/tests/.php_cd.php src/Akeneo/Connectivity/Connection/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Connectivity/Connection/back/tests/.php_cd.php src/Akeneo/Connectivity/Connection/back

connectivity-connection-lint-back:
ifneq ($(CI),true)
	$(DOCKER_COMPOSE) run --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 --rm php bin/console cache:warmup
endif
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Connectivity/Connection/back/tests/.php_cs.php
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--level=8 \
		--configuration src/Akeneo/Connectivity/Connection/back/tests/phpstan.neon \
		src/Akeneo/Connectivity/Connection/back/Application \
		src/Akeneo/Connectivity/Connection/back/Domain
	$(PHP_RUN) vendor/bin/phpstan analyse \
		--level=5 \
		--configuration src/Akeneo/Connectivity/Connection/back/tests/phpstan.neon \
		src/Akeneo/Connectivity/Connection/back/Infrastructure
	$(PHP_RUN) bin/console lint:container

connectivity-connection-lint-back_fix:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --config=src/Akeneo/Connectivity/Connection/back/tests/.php_cs.php

connectivity-connection-unit-back:
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T --rm php php vendor/bin/phpspec run --format=junit > var/tests/phpspec/specs.xml
	tests/scripts/find_non_executed_phpspec.sh
endif
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Connectivity/Connection/back/tests/Unit/spec/
	# Scope Mapper unit tests
	$(PHP_RUN) vendor/bin/phpspec run tests/back/Pim/Structure/Specification/Component/Security/
	$(PHP_RUN) vendor/bin/phpspec run tests/back/Pim/Enrichment/Specification/Component/Security/
	$(PHP_RUN) vendor/bin/phpspec run tests/back/Channel/Specification/Infrastructure/Component/Security/

connectivity-connection-critical-e2e: var/tests/behat/connectivity/connection
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy -s connectivity src/Akeneo/Connectivity/Connection/tests/features/activate_an_app.feature
	APP_ENV=behat $(PHP_RUN) vendor/bin/behat --config behat.yml -p legacy -s connectivity src/Akeneo/Connectivity/Connection/tests/features/edit_connection.feature

connectivity-connection-integration-back:
ifeq ($(CI),true)
	tests/scripts/run_phpunit.sh . tests/scripts/find_phpunit.php Akeneo_Connectivity_Connection_Integration
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_Integration --log-junit var/tests/phpunit/phpunit_connectivity_integration.xml $(0)
endif

connectivity-connection-e2e-back:
ifeq ($(CI),true)
	tests/scripts/run_phpunit.sh . tests/scripts/find_phpunit.php Akeneo_Connectivity_Connection_EndToEnd
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_EndToEnd --log-junit var/tests/phpunit/phpunit_connectivity_e2e.xml $(0)
endif

connectivity-connection-back:
	$(MAKE) connectivity-connection-coupling-back
	$(MAKE) connectivity-connection-lint-back
	$(MAKE) connectivity-connection-unit-back
	$(MAKE) connectivity-connection-integration-back
	$(MAKE) connectivity-connection-e2e-back

# Tests Front

connectivity-connection-unit-front:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) jest --ci ${O}
	$(_PERMISSION_FORM_YARN_RUN) jest --ci --coverage ${O}

connectivity-connection-lint-front:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) eslint
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) prettier --check
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) tsc --noEmit --strict
	$(_PERMISSION_FORM_YARN_RUN) eslint
	$(_PERMISSION_FORM_YARN_RUN) prettier --check
	$(_PERMISSION_FORM_YARN_RUN) tsc --noEmit --strict

# Development

connectivity-connection-unit-front_coverage:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) jest --coverage

connectivity-connection-unit-front_watch:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) jest --watchAll

connectivity-connection-lint-front_fix:
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) eslint --fix
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) prettier --write
	$(_PERMISSION_FORM_YARN_RUN) eslint --fix
	$(_PERMISSION_FORM_YARN_RUN) prettier --write

# Analysis tools
connectivity-connection-coverage:
	# run the backend application unit tests on scope connectivity
	XDEBUG_MODE=coverage $(PHP_RUN) vendor/bin/phpspec run \
    		-c src/Akeneo/Connectivity/Connection/back/tests/phpspec.yml.dist \
    		src/Akeneo/Connectivity/Connection/back/tests/Unit/spec/
	# run the backend application integration tests on scope connectivity
	XDEBUG_MODE=coverage APP_ENV=test ${PHP_RUN} vendor/bin/phpunit \
		-c src/Akeneo/Connectivity/Connection/back/tests/ \
		--coverage-clover coverage/Connectivity/Back/Integration/coverage.cov \
		--coverage-php coverage/Connectivity/Back/Integration/coverage.php \
		--coverage-html coverage/Connectivity/Back/Integration/ \
		--testsuite Integration $(0)
	# run the backend application end to end tests on scope connectivity
	XDEBUG_MODE=coverage APP_ENV=test ${PHP_RUN} vendor/bin/phpunit \
		-c src/Akeneo/Connectivity/Connection/back/tests/ \
		--coverage-clover coverage/Connectivity/Back/EndToEnd/coverage.cov \
		--coverage-php coverage/Connectivity/Back/EndToEnd/coverage.php \
		--coverage-html coverage/Connectivity/Back/EndToEnd/ \
		--testsuite EndToEnd $(0)

	$(DOCKER_COMPOSE) run --rm php mkdir -p var/tests/behat/connectivity/connection
	# download phpcov binary
	$(DOCKER_COMPOSE) run --rm php sh -c "test -e phpcov.phar || wget https://phar.phpunit.de/phpcov.phar && php phpcov.phar --version"
	# create a coverage global folder
	$(DOCKER_COMPOSE) run --rm php sh -c "\
		if [ -d coverage/Connectivity/Back/Global/ ]; then rm -r coverage/Connectivity/Back/Global/; fi && \
		mkdir -p coverage/Connectivity/Back/Global/ && \
		cp coverage/Connectivity/Back/Unit/coverage.php coverage/Connectivity/Back/Global/Unit.cov && \
		cp coverage/Connectivity/Back/Integration/coverage.php coverage/Connectivity/Back/Global/Integration.cov && \
		cp coverage/Connectivity/Back/EndToEnd/coverage.php coverage/Connectivity/Back/Global/EndToEnd.cov"
	# run the command to merge all the code coverage on scope connectivity
	XDEBUG_MODE=coverage ${PHP_RUN} -d memory_limit=-1 phpcov.phar merge \
		--clover coverage/Connectivity/Back/Global/coverage.cov \
		--html coverage/Connectivity/Back/Global/ \
		coverage/Connectivity/Back/Global/

connectivity-connection-insight:
	$(PHP_RUN) vendor/bin/phpinsights analyse --summary --no-interaction --config-path=src/Akeneo/Connectivity/Connection/back/tests/phpinsights.php src/Akeneo/Connectivity/Connection/back

connectivity-connection-psalm:
	$(PHP_RUN) vendor/bin/psalm -c src/Akeneo/Connectivity/Connection/back/tests/psalm.xml

connectivity-connection-unused-coupling-rules:
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Connectivity/Connection/back/tests/.php_cd.php src/Akeneo/Connectivity/Connection/back
