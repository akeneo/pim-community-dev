.PHONY: job-lint-back
job-lint-back: #Doc: launch PHPStan for job bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Platform/Job/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Platform/Job/back/tests/.php_cs.php
	${PHP_RUN} vendor/bin/rector process --dry-run --config src/Akeneo/Platform/Job/back/tests/rector.php

.PHONY: job-lint-fix-back
job-lint-fix-back: #Doc: launch PHPStan for job bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Platform/Job/back/tests/.php_cs.php
	${PHP_RUN} vendor/bin/rector process --config src/Akeneo/Platform/Job/back/tests/rector.php

.PHONY: job-coupling-back
job-coupling-back: #Doc: launch coupling detector for job bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Platform/Job/back/tests/.php_cd.php src/Akeneo/Platform/Job/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Platform/Job/back/tests/.php_cd.php src/Akeneo/Platform/Job/back

.PHONY: job-unit-back
job-unit-back: #Doc: launch PHPSpec for job bounded context
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Platform/Job/back/tests/Specification

.PHONY: job-integration-back
job-integration-back: #Doc: launch PHPUnit integration tests for job bounded context
ifeq ($(CI),true)
	tests/scripts/run_phpunit.sh src/Akeneo/Platform/Job/back/tests tests/scripts/find_phpunit.php Job_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Job/back/tests --testsuite Job_Integration_Test $(O)
endif

.PHONY: job-acceptance-back
job-acceptance-back: #Doc: launch PHPUnit acceptance tests for job bounded context
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Platform/Job/back/tests --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite Job_Acceptance_Test $(O)

.PHONY: job-ci-back
job-ci-back: job-lint-back job-coupling-back job-unit-back job-acceptance-back job-integration-back

.PHONY: job-ci-front
job-ci-front:
	$(YARN_RUN) workspace @akeneo-pim-community/process-tracker lint:check
	$(YARN_RUN) workspace @akeneo-pim-community/process-tracker test:unit:run

.PHONY: job-ci
job-ci: job-ci-back job-ci-front
