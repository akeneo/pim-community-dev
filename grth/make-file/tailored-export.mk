.PHONY: lint-back
lint-back: #Doc: launch PHPStan for tailored export
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/tailored-export/back/tests/phpstan-grth.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php components/tailored-export/back

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for tailored export
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/tailored-export/back/tests/.php_cd.php components/tailored-export/back/src

.PHONY: unit-back
unit-back: #Doc: launch PHPSpec for tailored export
	$(PHP_RUN) vendor/bin/phpspec run components/tailored-export/back/tests/Specification

.PHONY: integration-back
integration-back: #Doc: launch PHPUnit integration tests for tailored export
ifeq ($(CI),true)
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c . --testsuite TailoredExport_Integration_Test --log-junit var/tests/phpunit/phpunit_tailored-export-integration.xml $(O)
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c . --testsuite TailoredExport_Integration_Test $(O)
endif

.PHONY: acceptance-back
acceptance-back: #Doc: launch PHPUnit acceptance tests for tailored export
ifeq ($(CI),true)
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/tailored-export/back/tests/phpunit-grth.xml --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite TailoredExport_Acceptance_Test
else
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/tailored-export/back/tests/phpunit-grth.xml --testsuite TailoredExport_Acceptance_Test $(O)
endif

.PHONY: ci-back
ci-back: lint-back coupling-back unit-back acceptance-back integration-back

.PHONY: ci-front
ci-front:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/tailored-export lint:check
	$(YARN_RUN) workspace @akeneo-pim-enterprise/tailored-export test:unit:run

.PHONY: ci
ci: ci-back ci-front
