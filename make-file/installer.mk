.PHONY: installer-lint-back
installer-lint-back: #Doc: launch PHPStan for installer
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Platform/Installer/back/tests/phpstan.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Platform/Installer/back/tests/.php_cs.src.php
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Platform/Installer/back/tests/.php_cs.tests.php
	${PHP_RUN} vendor/bin/rector process --dry-run --config=src/Akeneo/Platform/Installer/back/tests/rector.php

.PHONY: installer-lint-fix-back
installer-lint-fix-back: #Doc: launch PHP CS fixer for installer
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Platform/Installer/back/tests/.php_cs.src.php
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Platform/Installer/back/tests/.php_cs.tests.php
	${PHP_RUN} vendor/bin/rector process --config=src/Akeneo/Platform/Installer/back/tests/rector.php

.PHONY: installer-coupling-back
installer-coupling-back: #Doc: launch coupling detector for installer
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Installer/back/tests/.php_cd.php src/Akeneo/Platform/Installer/back/src
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Platform/Installer/back/tests/.php_cd.php src/Akeneo/Platform/Installer/back/src

.PHONY: installer-unit-back
installer-unit-back: #Doc: launch PHPSpec for installer
	$(PHP_RUN) vendor/bin/phpspec run -vvv src/Akeneo/Platform/Installer/back/tests/Specification

.PHONY: installer-integration-back
installer-integration-back: #Doc: launch PHPUnit integration tests for installer
ifeq ($(CI),true)
	.circleci/run_phpunit.sh src/Akeneo/Platform/Installer/back/tests/phpunit.xml .circleci/find_phpunit.php Installer_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Installer/back/tests/phpunit.xml --testsuite Installer_Integration_Test $(O)
endif

.PHONY: installer-acceptance-back
installer-acceptance-back: #Doc: launch PHPUnit acceptance tests for installer
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Installer/back/tests/phpunit.xml --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite Installer_Acceptance_Test

.PHONY: installer-ci
installer-ci: installer-lint-back installer-coupling-back installer-unit-back installer-acceptance-back installer-integration-back
