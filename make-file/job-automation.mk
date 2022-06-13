.PHONY: lint-back
lint-back: #Doc: launch PHPStan for job automation
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/job-automation/back/tests/phpstan-ee.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/job-automation/back/tests/.php_cs.php

.PHONY: lint-fix-back
lint-fix-back: #Doc: launch PHP CS fixer for job automation
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=components/job-automation/back/tests/.php_cs.php

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for job automation
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/job-automation/back/tests/.php_cd.php components/job-automation/back/src

.PHONY: unit-back
unit-back: #Doc: launch PHPSpec for job automation
	$(PHP_RUN) vendor/bin/phpspec run components/job-automation/back/tests/Specification

.PHONY: integration-back
integration-back: #Doc: launch PHPUnit integration tests for job automation
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c components/job-automation/back/tests/phpunit-ee.xml --testsuite JobAutomation_Integration_Test $(O)

.PHONY: acceptance-back
acceptance-back: #Doc: launch PHPUnit acceptance tests for job automation
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/job-automation/back/tests/phpunit-ee.xml --testsuite JobAutomation_Acceptance_Test $(O)

.PHONY: lint-front
lint-front:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/job-automation lint:check

.PHONY: lint-fix-front
lint-fix-front:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/job-automation lint:fix

.PHONY: unit-front
unit-front:
	$(YARN_RUN) workspace @akeneo-pim-enterprise/job-automation test:unit:run

.PHONY: ci-back
ci-back: lint-back coupling-back unit-back acceptance-back integration-back

.PHONY: ci-front
ci-front: lint-front unit-front

.PHONY: ci
ci: ci-back ci-front
