##
## Target used run command related on Asset Bounded context
##

.PHONY: asset-coupling
asset-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Asset/.php_cd.php src/Akeneo/Asset
