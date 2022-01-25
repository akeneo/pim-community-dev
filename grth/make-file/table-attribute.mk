include make-file/dev.mk
include make-file/test.mk

.PHONY: table-attribute-coupling-back
table-attribute-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect src/Akeneo/Pim/TableAttribute/back/ --config-file src/Akeneo/Pim/TableAttribute/tests/back/.php_cd.php

.PHONY: table-attribute-static-back
table-attribute-static-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/TableAttribute/tests/back/phpstan.domain.neon
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/TableAttribute/tests/back/phpstan.infra.neon

.PHONY: table-attribute-unit-back
table-attribute-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run --config=src/Akeneo/Pim/TableAttribute/tests/back/phpspec.yml.dist $(O)

.PHONY: table-attribute-acceptance-back
table-attribute-acceptance-back: var/tests/behat/table-attribute
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Pim/TableAttribute/tests/back/behat.yml --suite=acceptance --format pim --out var/tests/behat/table-attribute --format progress --out std --colors $(O)

.PHONY: table-attribute-integration-back
table-attribute-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_Integration $(O)

.PHONY: table-attribute-end-to-end-back
table-attribute-end-to-end-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_End_To_End $(O)

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
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/TableAttribute/front jest --ci $(O)

.PHONY: table-attribute-lint-front
table-attribute-lint-front:
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/TableAttribute/front lint $(O)

.PHONY: table-attribute-prettier-check-front
table-attribute-prettier-check-front:
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/TableAttribute/front prettier-check
