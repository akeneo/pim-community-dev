##
## Target used run command related on Asset Bounded context
##

.PHONY: asset-coupling
asset-coupling: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Asset/.php_cd.php src/Akeneo/Asset
