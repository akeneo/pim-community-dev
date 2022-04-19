.PHONY: lint-back
lint-back: #Doc: launch PHPStan for tailored import
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/tailored-import/back/tests/phpstan-grth.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/tailored-import/back/tests/.php_cs.php components/tailored-import/back

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for tailored import
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/tailored-import/back/tests/.php_cd.php components/tailored-import/back/src

.PHONY: unit-back
unit-back: #Doc: launch PHPSpec for tailored import
	$(PHP_RUN) vendor/bin/phpspec run components/tailored-import/back/tests/Specification

.PHONY: integration-back
integration-back: #Doc: launch PHPUnit integration tests for tailored import
ifeq ($(CI),true)
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c components/tailored-import/back/tests/phpunit-grth.xml --testsuite TailoredImport_Integration_Test --log-junit var/tests/phpunit/phpunit_tailored-import-integration.xml $(O)
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c components/tailored-import/back/tests/phpunit-grth.xml --testsuite TailoredImport_Integration_Test $(O)
endif

.PHONY: acceptance-back
acceptance-back: #Doc: launch PHPUnit acceptance tests for tailored import
ifeq ($(CI),true)
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/tailored-import/back/tests/phpunit-grth.xml --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite TailoredImport_Acceptance_Test
else
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/tailored-import/back/tests/phpunit-grth.xml --testsuite TailoredImport_Acceptance_Test $(O)
endif

.PHONY: ci-back
ci-back: lint-back coupling-back unit-back acceptance-back integration-back

.PHONY: ci-front
ci-front:
	$(YARN_RUN) run --cwd=components/tailored-import/front test:unit:run
	$(YARN_RUN) run --cwd=components/tailored-import/front lint:check

.PHONY: ci
ci: ci-back ci-front
