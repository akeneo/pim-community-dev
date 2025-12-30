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

_COMMUNICATION_CHANNEL_YARN_RUN = $(YARN_RUN) run --cwd=src/Akeneo/Platform/Bundle/CommunicationChannelBundle/Resources/workspaces/communication-channel/

# Tests Back

communication-channel-lint-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --level=8 src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Application src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Domain
	$(PHP_RUN) vendor/bin/phpstan analyse --level=5 src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/Infrastructure

communication-channel-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/.php_cd.php src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/.php_cd.php src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back

communication-channel-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/Unit/spec/

communication-channel-integration-back:
ifeq ($(CI),true)
	tests/scripts/run_phpunit.sh . tests/scripts/find_phpunit.php Akeneo_Communication_Channel_Integration
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite Akeneo_Communication_Channel_Integration $(0)
endif

# Tests Front

communication-channel-unit-front:
	$(YARN_RUN) unit --coverage=false src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/tests/front/unit

# Developpement

communication-channel-back:
	$(DOCKER_COMPOSE) run --rm php rm -rf var/cache/dev
	APP_ENV=dev $(DOCKER_COMPOSE) run -e APP_DEBUG=1 --rm php bin/console cache:warmup
	${PHP_RUN} vendor/bin/php-cs-fixer fix --config=.php_cs.php src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back
	$(MAKE) communication-channel-lint-back
	$(MAKE) communication-channel-coupling-back
	$(MAKE) communication-channel-unit-back
	$(MAKE) communication-channel-integration-back

communication-channel-front:
	$(NODE_RUN) node_modules/.bin/prettier --config .prettierrc.json --parser typescript --write "./src/Akeneo/Platform/Bundle/CommunicationChannelBundle/**/*.{ts,tsx}"
	$(YARN_RUN) unit

# Generate Models

communication-channel-generate-models:
	$(_COMMUNICATION_CHANNEL_YARN_RUN) generate-models
