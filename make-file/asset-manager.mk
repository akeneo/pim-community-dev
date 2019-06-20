##
## Target used run command related on reference entity bounded context
##

.PHONY: asset-manager-coupling
asset-manager-coupling: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/AssetManager/tests/back/.php_cd.php src/Akeneo/AssetManager/back

