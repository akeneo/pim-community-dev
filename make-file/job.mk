.PHONY: lint-back
lint-back: #Doc: launch PHPStan for job bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Platform/Job/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/Platform/Job

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for job bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Job/back/tests/.php_cd.php src/Akeneo/Platform/Job/back

.PHONY: unit-back
unit-back: #Doc: launch PHPSpec for job bounded context
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Platform/Job/back/tests/Specification

.PHONY: integration-back
integration-back: #Doc: launch PHPUnit integration tests for job bounded context
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh src/Akeneo/Platform/Job/back/tests vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Job_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Job/back/tests --testsuite Job_Integration_Test $(O)
endif

.PHONY: acceptance-back
acceptance-back: #Doc: launch PHPUnit acceptance tests for job bounded context
ifeq ($(CI),true)
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Job/back/tests --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite Job_Acceptance_Test
else
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Job/back/tests --testsuite Job_Acceptance_Test $(O)
endif

.PHONY: ci-back
ci-back: lint-back coupling-back unit-back acceptance-back integration-back
