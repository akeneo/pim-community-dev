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

communication-channel-static-analysis-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/phpstan.neon
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/phpstan-infra.neon

communication-channel-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/.php_cd.php src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back

communication-channel-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back/tests/Unit/spec/

communication-channel-integration-back:
ifeq ($(CI),true)
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php Akeneo_Communication_Channel_Integration
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite Akeneo_Communication_Channel_Integration $(0)
endif

# Tests Front

communication-channel-front-unit:
	$(YARN_RUN) unit --coverage=false src/Akeneo/Platform/Bundle/CommunicationChannelBundle/front/tests/front/unit

# Developpement

communication-channel-back:
	${PHP_RUN} vendor/bin/php-cs-fixer fix --config=.php_cs.php src/Akeneo/Platform/Bundle/CommunicationChannelBundle/back
	$(MAKE) communication-channel-static-analysis-back
	$(MAKE) communication-channel-coupling-back
	$(MAKE) communication-channel-unit-back
	$(MAKE) communication-channel-integration-back

communication-channel-front:
	$(NODE_RUN) node_modules/.bin/prettier --config .prettierrc.json --parser typescript --write "./src/Akeneo/Platform/Bundle/CommunicationChannelBundle/**/*.ts"
	$(YARN_RUN) unit

# Generate Models

communication-channel-generate-models:
	$(_COMMUNICATION_CHANNEL_YARN_RUN) generate-models
