.PHONY: channel-lint-back
channel-lint-back: #Doc: launch PHPStan for channel bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Channel/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Channel/back/tests/.php_cs.php

.PHONY: channel-lint-fix-back
channel-lint-fix-back: #Doc: launch PHPStan for channel bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=.php_cs.php src/Akeneo/Channel

.PHONY: channel-coupling-back
channel-coupling-back: #Doc: launch coupling detector for channel bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Channel/back/tests/.php_cd.php src/Akeneo/Channel/back

.PHONY: channel-unit-back
channel-unit-back: #Doc: launch PHPSpec for channel bounded context
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Channel/back/tests/Specification

.PHONY: channel-integration-back
channel-integration-back: #Doc: launch PHPUnit integration tests for channel bounded context
ifeq ($(CI),true)
	.circleci/run_phpunit.sh src/Akeneo/Channel/back/tests .circleci/find_phpunit.php Channel_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Channel/back/tests --testsuite Channel_Integration_Test $(O)
endif

.PHONY: channel-acceptance-back
channel-acceptance-back: #Doc: launch PHPUnit acceptance tests for channel bounded context
ifeq ($(CI),true)
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Channel/back/tests --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite Channel_Acceptance_Test
else
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Channel/back/tests --testsuite Channel_Acceptance_Test $(O)
endif

.PHONY: channel-ci-back
channel-ci-back: channel-lint-back channel-coupling-back channel-unit-back channel-acceptance-back channel-integration-back

.PHONY: channel-ci
channel-ci: channel-ci-back
