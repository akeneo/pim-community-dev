include make-file/dev.mk
include make-file/test.mk

.PHONY: table-attribute-coupling-back
table-attribute-coupling-back:
	$(DOCKER_COMPOSE) run --rm php sh -c "cd grth && php vendor/bin/php-coupling-detector detect src/Akeneo/Pim/TableAttribute/back/ --config-file src/Akeneo/Pim/TableAttribute/tests/back/.php_cd.php"
	$(DOCKER_COMPOSE) run --rm php sh -c "cd grth && php vendor/bin/php-coupling-detector list-unused-requirements src/Akeneo/Pim/TableAttribute/back/ --config-file src/Akeneo/Pim/TableAttribute/tests/back/.php_cd.php"

.PHONY: table-attribute-static-back
table-attribute-static-back:
	$(DOCKER_COMPOSE) run --rm php sh -c "php vendor/bin/phpstan analyse --configuration grth/src/Akeneo/Pim/TableAttribute/tests/back/phpstan.domain.neon"
	$(DOCKER_COMPOSE) run --rm php sh -c "php vendor/bin/phpstan analyse --configuration grth/src/Akeneo/Pim/TableAttribute/tests/back/phpstan.infra.neon"

.PHONY: table-attribute-unit-back
table-attribute-unit-back:
	$(DOCKER_COMPOSE) run --rm php sh -c "php ../vendor/bin/phpspec run --config=grth/src/Akeneo/Pim/TableAttribute/tests/back/phpspec.yml.dist $(O)"

.PHONY: table-attribute-acceptance-back
table-attribute-acceptance-back: var/tests/behat/table-attribute
	$(PHP_RUN) vendor/bin/behat --config grth/src/Akeneo/Pim/TableAttribute/tests/back/behat.yml --suite=acceptance_ee --format pim --out var/tests/behat/table-attribute --format progress --out std --colors $(O)

.PHONY: table-attribute-integration-back
table-attribute-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_Integration_EE $(O)

.PHONY: table-attribute-end-to-end-back
table-attribute-end-to-end-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_End_To_End_EE $(O)

.PHONY: table-attribute-lint-back
table-attribute-lint-back:
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php \
		src/Akeneo/Pim/TableAttribute/back \
		src/Akeneo/Pim/TableAttribute/tests/back/Acceptance/Context \
		src/Akeneo/Pim/TableAttribute/tests/back/Acceptance/InMemory \
		src/Akeneo/Pim/TableAttribute/tests/back/EndToEnd \
		src/Akeneo/Pim/TableAttribute/tests/back/Integration

.PHONY: table-attribute-unit-front
table-attribute-unit-front:
	$(NODE_RUN) sh -c "cd grth && yarn run --cwd=src/Akeneo/Pim/TableAttribute/front jest --ci $(O)"

.PHONY: table-attribute-lint-front
table-attribute-lint-front:
	$(NODE_RUN) sh -c "cd grth && yarn run --cwd=src/Akeneo/Pim/TableAttribute/front lint $(O)""

.PHONY: table-attribute-prettier-check-front
table-attribute-prettier-check-front:
	$(NODE_RUN) sh -c "cd grth && yarn run --cwd=src/Akeneo/Pim/TableAttribute/front prettier-check"

.PHONY: table-attribute-prettier-fix-front
table-attribute-prettier-fix-front:
	$(NODE_RUN) sh -c "cd grth && yarn run --cwd=src/Akeneo/Pim/TableAttribute/front prettier"