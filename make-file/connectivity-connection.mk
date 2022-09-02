include make-file/test.mk

_CONNECTIVITY_CONNECTION_YARN_RUN = $(YARN_RUN) run --cwd=vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/front/

# Tests Back

.PHONY: connectivity-connection-integration-back
connectivity-connection-integration-back:
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Akeneo_Connectivity_Connection_Integration
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c . --testsuite Akeneo_Connectivity_Connection_Integration --log-junit var/tests/phpunit/phpunit_connectivity_integration.xml $(0)
endif

.PHONY: connectivity-connection-unit-back
connectivity-connection-unit-back: var/tests/phpspec #Doc: launch PHPSec for connectivity-connection
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T --rm php php vendor/bin/phpspec run -c src/Akeneo/Connectivity/Connection/back/tests/phpspec.yml.dist --format=junit > var/tests/phpspec/connectivity-connection.xml
else
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/Connectivity/Connection/back/tests/phpspec.yml.dist $(O)
endif
