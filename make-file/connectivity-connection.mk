include make-file/test.mk

_CONNECTIVITY_CONNECTION_YARN_RUN = $(YARN_RUN) run --cwd=vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/front/

# Tests Back

.PHONY: connectivity-connection-unit-back
connectivity-connection-unit-back: var/tests/phpspec #Doc: launch PHPSec for connectivity-connection
ifeq ($(CI),true)
	$(DOCKER_COMPOSE) run -T --rm php php vendor/bin/phpspec run -c src/Akeneo/Connectivity/Connection/back/tests/phpspec.yml.dist --format=junit > var/tests/phpspec/connectivity-connection.xml
else
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/Connectivity/Connection/back/tests/phpspec.yml.dist $(O)
endif
