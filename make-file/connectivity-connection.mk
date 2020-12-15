include test.mk

_CONNECTIVITY_CONNECTION_YARN_RUN = $(YARN_RUN) run --cwd=vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/front/

# Tests Back

connectivity-connection-coupling-back: #Doc: launch coupling detector for connectivity
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/back/tests/.php_cd.php vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/back

connectivity-connection-lint-back: #Doc: launch PHPStan for connectivity
	$(PHP_RUN) vendor/bin/phpstan analyse --level=8 vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/back/Application vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/back/Domain
	$(PHP_RUN) vendor/bin/phpstan analyse --level=5 vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/back/Infrastructure

connectivity-connection-acceptance-back: var/tests/behat/connectivity/connection #Doc: launch Behat for connectivity
	${PHP_RUN} vendor/bin/behat --config vendor/akeneo/pim-community-dev/src/Akeneo/Connectivity/Connection/back/tests/Acceptance/behat.yml --no-interaction --format=progress --strict

# Tests Front

connectivity-connection-lint-front: #Doc: launch front linter for connectivity
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) eslint
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) prettier --check

connectivity-connection-unit-front: #Doc: launch front unit test for connectivity
	$(_CONNECTIVITY_CONNECTION_YARN_RUN) jest --ci
