.PHONY: coupling-back
coupling-back: #Doc: launch coupling detector for trial-edition
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/TrialEdition/tests/back/.php_cd.php src/Akeneo/TrialEdition/back

.PHONY: lint-back
lint-back: #Doc: launch PHPStan for trial-edition
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/TrialEdition/back
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration=src/Akeneo/TrialEdition/tests/back/phpstan.neon.dist

.PHONY: unit-back
unit-back: #Doc: launch PHPSec for trial-edition
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/TrialEdition/tests/back/Specification

.PHONY: tests
tests: lint-back coupling-back unit-back #Doc: launch all tests for trial-edition
