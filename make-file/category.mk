.PHONY: category-front-dev
category-front-dev:
	$(DOCKER_COMPOSE) -f docker-compose.yml -f src/Akeneo/Category/front/docker-compose.yml up -d --remove-orphans

.PHONY: category-lint-back
category-lint-back: #Doc: launch PHPStan for category bounded context
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Category/back/tests/phpstan.neon.dist
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=src/Akeneo/Category/back/tests/.php_cs.php

.PHONY: category-lint-front
category-lint-front:
	$(YARN_RUN) workspace @akeneo-pim-community/category lint:check

.PHONY: category-lint-fix-back
category-lint-fix-back: #Doc: launch PHPStan for category bounded context
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --config=src/Akeneo/Category/back/tests/.php_cs.php

.PHONY: category-lint-fix-front
category-lint-fix-front:
	$(YARN_RUN) workspace @akeneo-pim-community/category lint:fix

.PHONY: category-coupling-back
category-coupling-back: #Doc: launch coupling detector for category bounded context
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Category/back/tests/.php_cd.php src/Akeneo/Category/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Category/back/tests/.php_cd.php src/Akeneo/Category/back

.PHONY: category-unit-back
category-unit-back: #Doc: launch PHPSpec for category bounded context
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Category/back/tests/Specification
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Category/back/tests --testsuite Category_Unit_Test $(F)

.PHONY: category-unit-front
category-unit-front:
	$(YARN_RUN) workspace  @akeneo-pim-community/category test:unit:run

.PHONY: category-integration-back
category-integration-back: #Doc: launch PHPUnit integration tests for category bounded context
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Category/back/tests --testsuite Category_Integration_Test $(F)

.PHONY: category-end-to-end-back
category-end-to-end-back: #Doc: launch PHPUnit end-to-end tests for category bounded context
	APP_ENV=test $(PHP_RUN) vendor/bin/phpunit -c src/Akeneo/Category/back/tests --testsuite Category_EndToEnd_Test $(F)

.PHONY: category-ci-back
category-ci-back: category-lint-back category-coupling-back category-unit-back category-integration-back category-end-to-end-back

.PHONY: category-ci-front
category-ci-front: category-unit-front

.PHONY: category-ci
category-ci: category-ci-back category-ci-front
