.PHONY: lint-back
lint-back: #Doc: launch PHPStan for tailored export
	$(PHP_RUN) vendor/bin/phpstan analyse --level=8 src/Akeneo/Platform/Bundle/TailoredExportBundle/src

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for tailored export
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Bundle/TailoredExportBundle/tests/.php_cd.php src/Akeneo/Platform/Bundle/TailoredExportBundle/src

.PHONY: unit-back
unit-back: #Doc: launch PHPSpec for tailored export
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Platform/Bundle/TailoredExportBundle/tests/Specification

.PHONY: integration-back
integration-back: # Disabled dependency becaused failed on custom workflow var/tests/phpunit #Doc: launch PHPUnit integration tests for tailored export
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php TailoredExport_Integration_Test
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite TailoredExport_Integration_Test $(O)
endif

.PHONY: acceptance-back
acceptance-back: #Doc: launch PHPUnit acceptance tests for tailored export
ifeq ($(CI),true)
	APP_ENV=test_fake ${PHP_RUN} vendor/bin/phpunit -c src/Akeneo/Platform/Bundle/TailoredExportBundle/tests --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite TailoredExport_Acceptance_Test
else
	APP_ENV=test_fake ${PHP_RUN} vendor/bin/phpunit -c src/Akeneo/Platform/Bundle/TailoredExportBundle/tests --testsuite TailoredExport_Acceptance_Test $(O)
endif
