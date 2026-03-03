.PHONY: measurement-lint-back
measurement-lint-back: #Doc: launch PHPStan for Measurement bounded context
	${PHP_RUN} vendor/bin/rector process --dry-run --config src/Akeneo/Tool/Bundle/MeasureBundle/back/tests/rector.php

.PHONY: measurement-lint-fix-back
measurement-lint-fix-back: #Doc: launch PHPStan for Measurement bounded context
	${PHP_RUN} vendor/bin/rector process --config src/Akeneo/Tool/Bundle/MeasureBundle/back/tests/rector.php

.PHONY: measurement-acceptance-back
measurement-acceptance-back: #Doc: launch PHPUnit acceptance tests for Measurement bounded context
ifeq ($(CI),true)
	.circleci/run_phpunit.sh . .circleci/find_phpunit.php Akeneo_Measurement_Acceptance
else
	APP_ENV=test $(PHP_RUN) ./vendor/bin/phpunit -c . --testsuite Akeneo_Measurement_Acceptance
endif
