.PHONY: measurement-lint-back
measurement-lint-back: #Doc: launch PHPStan for measurement bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Tool/Bundle/MeasureBundle/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Tool/Bundle/MeasureBundle/tests/.php_cs.php

.PHONY: measurement-lint-fix-back
measurement-lint-fix-back: #Doc: launch PHPStan for measurement bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Tool/Bundle/MeasureBundle/tests/.php_cs.php

.PHONY: measurement-coupling-back
measurement-coupling-back: #Doc: launch coupling detector for measurement bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Tool/Bundle/MeasureBundle/tests/.php_cd.php src/Akeneo/Tool/Bundle/MeasureBundle

.PHONY: measurement-unit-back
measurement-unit-back: #Doc: launch PHPSpec for measurement bounded context
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Tool/Bundle/MeasureBundle/spec $(O)

.PHONY: measurement-integration-back
measurement-integration-back: #Doc: launch PHPUnit integration tests for measurement bounded context
ifeq ($(CI),true)
	.circleci/run_phpunit.sh src/Akeneo/Tool/Bundle/MeasureBundle/tests .circleci/find_phpunit.php Measurement_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Tool/Bundle/MeasureBundle/tests --testsuite Measurement_Integration_Test $(O)
endif

.PHONY: measurement-acceptance-back
measurement-acceptance-back: #Doc: launch PHPUnit acceptance tests for measurement bounded context
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Tool/Bundle/MeasureBundle/tests --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite Measurement_Acceptance_Test  $(O)
