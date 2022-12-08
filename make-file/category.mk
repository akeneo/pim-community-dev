.PHONY: category-lint-back # CE and EE
category-lint-back: #Doc: launch PHPStan for category bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration vendor/akeneo/pim-community-dev/src/Akeneo/Category/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=vendor/akeneo/pim-community-dev/src/Akeneo/Category/back/tests/.php_cs.php

	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Category/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Category/back/tests/.php_cs.php

.PHONY: category-lint-fix-back # CE and EE
category-lint-fix-back: #Doc: launch PHPStan for category bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=vendor/akeneo/pim-community-dev/src/Akeneo/Category/back/tests/.php_cs.php

	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Category/back/tests/.php_cs.php

.PHONY: category-coupling-back # CE and EE
category-coupling-back: #Doc: launch coupling detector for category bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=vendor/akeneo/pim-community-dev/src/Akeneo/Category/back/tests/.php_cd.php vendor/akeneo/pim-community-dev/src/Akeneo/Category/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=vendor/akeneo/pim-community-dev/src/Akeneo/Category/back/tests/.php_cd.php vendor/akeneo/pim-community-dev/src/Akeneo/Category/back

	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Category/back/tests/.php_cd.php src/Akeneo/Category/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Category/back/tests/.php_cd.php src/Akeneo/Category/back

.PHONY: category-unit-back # CE and EE
category-unit-back: #Doc: launch PHPSpec for category bounded context
	$(DOCKER_COMPOSE) run --rm php sh -c "cd vendor/akeneo/pim-community-dev && php ../../../vendor/bin/phpspec run src/Akeneo/Category/back/tests/Specification"

	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Category/back/tests/Specification

.PHONY: category-integration-back # Only EE
category-integration-back: #Doc: launch PHPUnit integration tests for category bounded context
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Category/back/tests --testsuite EE_Category_Integration_Test $(F)

.PHONY: category-ci-back
category-ci-back: category-lint-back category-coupling-back category-unit-back category-integration-back

.PHONY: category-ci
category-ci: category-ci-back
