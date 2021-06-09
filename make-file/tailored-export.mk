.PHONY: lint-back
lint-back: #Doc: launch PHPStan for tailored export
	$(PHP_RUN) vendor/bin/phpstan analyse --level=8 src/Akeneo/Platform/Bundle/TailoredExportBundle/src

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for tailored export
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Bundle/TailoredExportBundle/tests/.php_cd.php src/Akeneo/Platform/Bundle/TailoredExportBundle/src

.PHONY: unit-back
unit-back: #Doc: launch PHPSec for tailored export
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Platform/Bundle/TailoredExportBundle/tests/Specification

.PHONY: integration-back
integration-back: var/tests/phpunit #Doc: launch PHP unit tests for tailored export
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php TailoredExport_Integration_Test
else
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit -c . --testsuite TailoredExport_Integration_Test $(O)
endif
