.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for the free trial
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/FreeTrial/tests/back/.php_cd.php src/Akeneo/FreeTrial/back

.PHONY: lint-back
lint-back: #Doc: launch PHPStan for the free trial
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/FreeTrial/back
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration=src/Akeneo/FreeTrial/tests/back/phpstan.neon.dist

.PHONY: unit-back
unit-back: #Doc: launch PHPSec for the free trial
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/FreeTrial/tests/back/Specification

.PHONY: tests
tests: lint-back coupling-back unit-back #Doc: launch all tests for the free trial
