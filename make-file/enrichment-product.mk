include make-file/dev.mk
include make-file/test.mk

.PHONY: enrichment-product-unit-back
enrichment-product-unit-back:
	$(DOCKER_COMPOSE) run --rm php sh -c "cd vendor/akeneo/pim-community-dev && php ../../../vendor/bin/phpspec run --config=src/Akeneo/Pim/Enrichment/Product/back/Test/phpspec.yml $(O)"

.PHONY: enrichment-product-integration-back
enrichment-product-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Enrichment_Product_EE $(O)
