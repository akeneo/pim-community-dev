##
## Target used run command related on Enrichment Bounded context
##

.PHONY: enrichment-coupling
enrichment-coupling:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/.php_cd.php src/Akeneo/Pim/Enrichment
