include make-file/dev.mk
include make-file/test.mk

.PHONY: table-attribute-acceptance-back
table-attribute-acceptance-back: var/tests/behat/table-attribute
	$(PHP_RUN) vendor/bin/behat --config grth/src/Akeneo/Pim/TableAttribute/tests/back/behat.yml --suite=acceptance_ee --format pim --out var/tests/behat/table-attribute --format progress --out std --colors $(O)

.PHONY: table-attribute-integration-back
table-attribute-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_Integration_EE $(O)

.PHONY: table-attribute-end-to-end-back
table-attribute-end-to-end-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Table_Attribute_End_To_End_EE $(O)
