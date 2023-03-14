.PHONY: enrichment-category-acceptance-back
enrichment-category-acceptance-back: var/tests/behat/enrichment-category
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Pim/Enrichment/Product/back/Test/behat.yml --suite=acceptance --format pim --out var/tests/behat/enrichment-category --format progress --out std --colors $(O)
