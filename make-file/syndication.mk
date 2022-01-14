.PHONY: lint-back
lint-back: #Doc: launch PHPStan for syndication
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration components/syndication/back/tests/phpstan-ee.neon
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=components/syndication/back/tests/.php_cs.php components/syndication/back

.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for syndication
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=components/syndication/back/tests/.php_cd.php components/syndication/back/src

.PHONY: unit-back
unit-back: #Doc: launch PHPSpec for syndication
	$(PHP_RUN) vendor/bin/phpspec run components/syndication/back/tests/Specification

.PHONY: integration-back
integration-back: # Disabled dependency becaused failed on custom workflow var/tests/phpunit #Doc: launch PHPUnit integration tests for syndication
ifeq ($(CI),true)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh . vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php Syndication_Integration_Test
else
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c . --testsuite Syndication_Integration_Test $(O)
endif

.PHONY: acceptance-back
acceptance-back: #Doc: launch PHPUnit acceptance tests for syndication
ifeq ($(CI),true)
	echo "skipped"
#	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/syndication/back/tests/phpunit-ee.xml --log-junit var/tests/phpunit/phpunit_$$(uuidgen).xml --testsuite Syndication_Acceptance_Test
else
	APP_ENV=test_fake $(PHP_RUN) vendor/bin/phpunit -c components/syndication/back/tests/phpunit-ee.xml --testsuite Syndication_Acceptance_Test $(O)
endif

.PHONY: ci-back
ci-back: lint-back coupling-back
# ci-back: lint-back coupling-back unit-back acceptance-back integration-back

.PHONY: ci-front
ci-front:
	$(YARN_RUN) run --cwd=components/syndication/front test:unit:run
	$(YARN_RUN) run --cwd=components/syndication/front lint:check

.PHONY: ci
ci: ci-back ci-front
