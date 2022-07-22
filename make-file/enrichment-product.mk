include make-file/dev.mk
include make-file/test.mk

.PHONY: enrichment-product-unit-back
enrichment-product-unit-back:
	$(DOCKER_COMPOSE) run --rm php sh -c "cd vendor/akeneo/pim-community-dev && php ../../../vendor/bin/phpspec run --config=src/Akeneo/Pim/Enrichment/Product/back/Test/phpspec.yml $(O)"

.PHONY: enrichment-product-integration-back
enrichment-product-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Enrichment_Product_EE $(O)

.PHONY: enrichment-product-acceptance-back
enrichment-product-acceptance-back: var/tests/behat/enrichment-product
	$(PHP_RUN) vendor/bin/behat --config vendor/akeneo/pim-community-dev/src/Akeneo/Pim/Enrichment/Product/back/Test/behat.yml --suite=acceptance_ee --format pim --out var/tests/behat/enrichment-product --format progress --out std --colors $(O)
