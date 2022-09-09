.PHONY: lint-back
lint-back: #Doc: launch PHPStan for tailored export
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/tailored-export/back/tests/phpstan-ee.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/tailored-export/back/tests/.php_cs.php components/tailored-export/back/src
	${PHP_RUN} vendor/bin/rector process --dry-run --config=components/tailored-export/back/tests/rector.php

.PHONY: lint-fix-back
lint-fix-back: #Doc: launch PHPStan for tailored export
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/tailored-export/back/tests/.php_cs.php components/tailored-export/back/src
	${PHP_RUN} vendor/bin/rector process --config=components/tailored-export/back/tests/rector.php

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for tailored export
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/tailored-export/back/tests/.php_cd.php components/tailored-export/back/src
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=components/tailored-export/back/tests/.php_cd.php components/tailored-export/back/src

.PHONY: unit-back
unit-back: #Doc: launch PHPSpec for tailored export
	$(PHP_RUN) vendor/bin/phpspec run components/tailored-export/back/tests/Specification

.PHONY: integration-back
integration-back: # Disabled dependency becaused failed on custom workflow var/tests/phpunit #Doc: launch PHPUnit integration tests for tailored export
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php TailoredExport_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c . --testsuite TailoredExport_Integration_Test $(O)
endif

.PHONY: acceptance-back
acceptance-back: #Doc: launch PHPUnit acceptance tests for tailored export
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/tailored-export/back/tests/phpunit-ee.xml --log-junit var/tests/phpunit/phpunit_tailored_export_acceptance.xml --testsuite TailoredExport_Acceptance_Test $(O)

.PHONY: ci-back
ci-back: lint-back coupling-back unit-back acceptance-back integration-back

.PHONY: unit-front
unit-front:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/tailored-export test:unit:run $(O)

.PHONY: lint-front
lint-front:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/tailored-export lint:check
	$(YARN_RUN) workspace @akeneo-pim-enterprise/tailored-export tsc --noEmit --strict --incremental false
	$(YARN_RUN) tsc -p components/tailored-export/back/tests/tsconfig.json

.PHONY: lint-fix-front
lint-fix-front:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/tailored-export lint:fix

.PHONY: ci-front
ci-front: lint-front unit-front

.PHONY: ci
ci: ci-back ci-front
