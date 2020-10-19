##
## Target used run command related on Enrichment bounded context
##

.PHONY: enrichment-lint-back
enrichment-lint-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --level=2 vendor/akeneo/pim-community-dev/src/Akeneo/Pim/Enrichment/Bundle
