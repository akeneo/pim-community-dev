.PHONY: channel-lint-back
channel-lint-back: #Doc: launch PHPStan for channel bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Channel/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Channel/back/tests/.php_cs.php

.PHONY: channel-lint-fix-back
channel-lint-fix-back: #Doc: launch PHPStan for channel bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Channel/back/tests/.php_cs.php

.PHONY: channel-coupling-back
channel-coupling-back: #Doc: launch coupling detector for channel bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Channel/back/tests/.php_cd.php src/Akeneo/Channel/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Channel/back/tests/.php_cd.php src/Akeneo/Channel/back

.PHONY: channel-unit-back
channel-unit-back: #Doc: launch PHPSpec for channel bounded context
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Channel/back/tests/Specification
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Channel/back/tests/Acceptance/Specification

.PHONY: channel-integration-back
channel-integration-back: #Doc: launch PHPUnit integration tests for channel bounded context
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Channel/back/tests --testsuite Channel_Integration_Test $(O)

.PHONY: channel-acceptance-back
channel-acceptance-back: #Doc: launch PHPUnit acceptance tests for channel bounded context
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Channel/back/tests --testsuite Channel_Acceptance_Test $(O)

.PHONY: channel-ci-back
channel-ci-back: channel-lint-back channel-coupling-back channel-unit-back channel-acceptance-back channel-integration-back

.PHONY: channel-ci
channel-ci: channel-ci-back
