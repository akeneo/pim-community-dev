.PHONY: enrichment-product-coupling-back
enrichment-product-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/Product/back/Test/.php_cd.php src/Akeneo/Pim/Enrichment/Product/back/API
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/Product/back/Test/.php_cd.php src/Akeneo/Pim/Enrichment/Product/back/Application
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/Product/back/Test/.php_cd.php src/Akeneo/Pim/Enrichment/Product/back/Domain
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/Product/back/Test/.php_cd.php src/Akeneo/Pim/Enrichment/Product/back/Infrastructure

.PHONY: enrichment-product-static-back
enrichment-product-static-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/Enrichment/Product/back/Test/phpstan.neon
