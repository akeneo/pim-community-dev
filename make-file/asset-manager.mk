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

.PHONY: asset-manager-coupling-back
asset-manager-coupling-back: #Doc: launch coupling detector for asset manager files
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/AssetManager/tests/back/.php_cd.php src/Akeneo/AssetManager/back

.PHONY: asset-manager-lint-back
asset-manager-lint-back: #Doc: launch PHP linter for the asset-manager
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/AssetManager/tests/back/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/AssetManager/back
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/Pim/Enrichment/AssetManager/

.PHONY: asset-manager-static-back
asset-manager-static-back: #Doc: launch PHP static analyzer for the asset manager
	$(PHP_RUN) src/Akeneo/AssetManager/tests/check-fake-implementations.php
	$(PHP_RUN) src/Akeneo/AssetManager/tests/check-requests-contracts-with-json-schemas.php

.PHONY: asset-manager-unit-back
asset-manager-unit-back: var/tests/phpspec #Doc: launch PHP unit test for the asset-manager
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T -u www-data --rm php php vendor/bin/phpspec run -c src/Akeneo/AssetManager/tests/back/phpspec.yml.dist --format=junit > var/tests/phpspec/asset-manager.xml
else
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/AssetManager/tests/back/phpspec.yml.dist $(O)
endif

.PHONY: asset-manager-acceptance-back
asset-manager-acceptance-back: var/tests/behat/asset-manager #Doc: launch Behat tests for the asset-manager
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/AssetManager/tests/back/behat.yml.dist --format pim --out var/tests/behat/asset-manager --format progress --out std --colors $(O)

.PHONY: asset-manager-acceptance-front
asset-manager-acceptance-front: #Doc: launch YARN acceptance tests for the asset-manager
	$(YARN_RUN) acceptance-am

.PHONY: asset-manager-integration-back
asset-manager-integration-back: var/tests/phpunit #Doc: launch PHPUnit integration test for the asset-manager
ifeq ($(CI),true)
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c src/Akeneo/AssetManager/tests/back --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite AssetFamily_Integration_Test
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c src/Akeneo/AssetManager/tests/back --testsuite AssetFamily_Integration_Test $(O)
endif
