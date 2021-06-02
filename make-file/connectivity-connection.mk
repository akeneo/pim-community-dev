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

connectivity-connection-lint-back:
	$(DOCKER_COMPOSE) run -u www-data --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 -u www-data --rm php bin/console cache:warmup
	$(PHP_RUN) vendor/bin/phpstan analyse --level=8 src/Akeneo/Connectivity/Connection/back/Application src/Akeneo/Connectivity/Connection/back/Domain
	$(PHP_RUN) vendor/bin/phpstan analyse --level=5 src/Akeneo/Connectivity/Connection/back/Infrastructure

connectivity-connection-unit-back:
	XDEBUG_MODE=coverage $(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/Connectivity/Connection/back/tests/phpspec.yml.dist src/Akeneo/Connectivity/Connection/back/tests/Unit/spec/

connectivity-connection-acceptance-back: var/tests/behat/connectivity/connection
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Connectivity/Connection/back/tests/Acceptance/behat.yml --format pim --out var/tests/behat/connectivity/connection --format progress --out std --colors

connectivity-connection-integration-back:
ifeq ($(CI),true)
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php Akeneo_Connectivity_Connection_Integration
else
	XDEBUG_MODE=coverage APP_ENV=test ${PHP_RUN} vendor/bin/phpunit \
		-c src/Akeneo/Connectivity/Connection/back/tests/ \
		--coverage-clover coverage/connectivity/integration/coverage.cov \
		--coverage-php coverage/connectivity/integration/coverage.php \
		--coverage-html coverage/connectivity/integration/ \
		--testsuite Integration $(0)
endif

connectivity-connection-e2e-back:
ifeq ($(CI),true)
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php Akeneo_Connectivity_Connection_EndToEnd
else
	XDEBUG_MODE=coverage APP_ENV=test ${PHP_RUN} vendor/bin/phpunit \
		-c src/Akeneo/Connectivity/Connection/back/tests/ \
		--coverage-clover coverage/connectivity/EndToEnd/coverage.cov \
		--coverage-php coverage/connectivity/EndToEnd/coverage.php \
		--coverage-html coverage/connectivity/EndToEnd/ \
		--testsuite EndToEnd $(0)
endif

connectivity-connection-codecoverage-merge: connectivity-connection-unit-back connectivity-connection-integration-back connectivity-connection-e2e-back
	test -e phpcov.phar || wget https://phar.phpunit.de/phpcov.phar
	php phpcov.phar --version
	if [ -d coverage/connectivity/fusion/ ]; then rm -r coverage/connectivity/fusion/; fi
	mkdir -p coverage/connectivity/fusion/
	cp coverage/connectivity/phpspec/coverage.php coverage/connectivity/fusion/phpspec.cov
	cp coverage/connectivity/integration/coverage.php coverage/connectivity/fusion/integration.cov
	cp coverage/connectivity/EndToEnd/coverage.php coverage/connectivity/fusion/EndToEnd.cov

	XDEBUG_MODE=coverage ${PHP_RUN} -d memory_limit=-1 phpcov.phar merge \
		--clover coverage/connectivity/fusion/coverage.cov \
		--html coverage/connectivity/fusion/ \
		coverage/connectivity/fusion/

connectivity-connection-back:
	$(MAKE) connectivity-connection-coupling-back
	$(MAKE) connectivity-connection-lint-back
	$(MAKE) connectivity-connection-unit-back
	$(MAKE) connectivity-connection-acceptance-back
	$(MAKE) connectivity-connection-integration-back
	$(MAKE) connectivity-connection-e2e-back

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
