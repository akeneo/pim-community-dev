.PHONY: import-export-lint-back
import-export-lint-back: #Doc: launch PHPStan for ImportExport bounded context
	$(DOCKER_COMPOSE) run --rm php php -d memory_limit=1G vendor/bin/phpstan analyse src/Akeneo/Platform/Bundle/ImportExportBundle --level 5
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Platform/Bundle/ImportExportBundle/Test/.php_cs.php

.PHONY: import-export-lint-fix-back
import-export-lint-fix-back: #Doc: launch PHPStan for ImportExport bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Platform/Bundle/ImportExportBundle/Test/.php_cs.php

.PHONY: import-export-coupling-back
import-export-coupling-back: #Doc: launch coupling detector for ImportExport bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Bundle/ImportExportBundle/Test/.php_cd.php src/Akeneo/Platform/Bundle/ImportExportBundle
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Platform/Bundle/ImportExportBundle/Test/.php_cd.php src/Akeneo/Platform/Bundle/ImportExportBundle

.PHONY: import-export-unit-back
import-export-unit-back: #Doc: launch PHPSpec for ImportExport bounded context
	$(PHP_RUN) vendor/bin/phpspec run tests/back/Platform/Specification/Bundle/ImportExportBundle

.PHONY: import-export-integration-back
import-export-integration-back: #Doc: launch PHPUnit integration tests for ImportExport bounded context
ifeq ($(CI),true)
	.circleci/run_phpunit.sh src/Akeneo/Platform/Bundle/ImportExportBundle/Test .circleci/find_phpunit.php ImportExport_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Bundle/ImportExportBundle/Test --testsuite ImportExport_Integration_Test $(O)
endif

.PHONY: import-export-acceptance-back
import-export-acceptance-back: #Doc: launch PHPUnit acceptance tests for ImportExport bounded context
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Bundle/ImportExportBundle/Test --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite ImportExport_Acceptance_Test $(O)

.PHONY: import-export-ci-back
import-export-ci-back: import-export-lint-back import-export-coupling-back import-export-unit-back import-export-acceptance-back import-export-integration-back

.PHONY: import-export-ci-front
import-export-ci-front:
	$(YARN_RUN) workspace @akeneo-pim-community/import-export lint:check
	$(YARN_RUN) workspace @akeneo-pim-community/import-export test:unit:run

.PHONY: import-export-ci
import-export-ci: import-export-ci-back import-export-ci-front
