.PHONY: table-attribute-coupling-back
table-attribute-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect src/Akeneo/Pim/TableAttribute/back/ --config-file src/Akeneo/Pim/TableAttribute/tests/back/.php_cd.php

.PHONY: table-attribute-static-back
table-attribute-static-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/TableAttribute/tests/back/phpstan.domain.neon
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/TableAttribute/tests/back/phpstan.infra.neon

.PHONY: table-attribute-unit-back
table-attribute-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Pim/TableAttribute/tests/back/Specification

.PHONY: table-attribute-acceptance-back
table-attribute-acceptance-back:
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Pim/TableAttribute/tests/back/behat.yml --format pim --out var/tests/behat/table-attribute --format progress --out std --colors $(O)

.PHONY: table-attribute-integration-back
table-attribute-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_Integration $(O)

.PHONY: table-attribute-end-to-end-back
table-attribute-end-to-end-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_End_To_End $(O)

.PHONY: rule-engine-unit-front
table-attribute-unit-front:
	$(YARN_RUN) run --cwd=src/Akeneo/Pim/TableAttribute/front jest --ci $(O)
