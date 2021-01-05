.PHONY: task-scheduling-unit-back
task-scheduling-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Pim/Automation/TaskScheduling/tests/back/Specification

.PHONY: task-scheduling-coupling-back
task-scheduling-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/TaskScheduling/tests/back/.php_cd.php src/Akeneo/Pim/Automation/TaskScheduling

.PHONY: task-scheduling-phpstan
task-scheduling-phpstan: var/cache/dev
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/Automation/TaskScheduling/tests/back/phpstan.neon.dist

.PHONY: task-scheduling-lint-back
task-scheduling-lint-back:
	$(PHP_RUN) vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php src/Akeneo/Pim/Automation/TaskScheduling/back
