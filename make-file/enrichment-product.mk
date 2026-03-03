.PHONY: enrichment-product-coupling-back
enrichment-product-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/Product/back/Test/.php_cd.php src/Akeneo/Pim/Enrichment/Product/back
	$(PHP_RUN) vendor/bin/php-coupling-detector list-unused-requirements --config-file=src/Akeneo/Pim/Enrichment/Product/back/Test/.php_cd.php src/Akeneo/Pim/Enrichment/Product/back

.PHONY: enrichment-product-static-back
enrichment-product-static-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/Pim/Enrichment/Product/back/Test/phpstan.neon

.PHONY: enrichment-product-unit-back
enrichment-product-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run --config=src/Akeneo/Pim/Enrichment/Product/back/Test/phpspec.yml $(O)

.PHONY: enrichment-product-lint-back
enrichment-product-lint-back:
	# Check all directories except Specification
	${PHP_RUN} vendor/bin/php-cs-fixer fix --diff --dry-run --config=.php_cs.php \
		src/Akeneo/Pim/Enrichment/Product/back/API \
		src/Akeneo/Pim/Enrichment/Product/back/Application \
		src/Akeneo/Pim/Enrichment/Product/back/Domain \
		src/Akeneo/Pim/Enrichment/Product/back/Infrastructure \
		src/Akeneo/Pim/Enrichment/Product/back/Test/Acceptance/Context \
		src/Akeneo/Pim/Enrichment/Product/back/Test/Acceptance/InMemory \
		src/Akeneo/Pim/Enrichment/Product/back/Test/Helper \
		src/Akeneo/Pim/Enrichment/Product/back/Test/Integration

.PHONY: enrichment-product-integration-back
enrichment-product-integration-back:
	APP_ENV=test ${PHP_RUN} vendor/bin/phpunit --configuration phpunit.xml.dist --testsuite Enrichment_Product $(O)

.PHONY: enrichment-product-acceptance-back
enrichment-product-acceptance-back: var/tests/behat/enrichment-product
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Pim/Enrichment/Product/back/Test/behat.yml --suite=acceptance --format pim --out var/tests/behat/enrichment-product --format progress --out std --colors $(O)
