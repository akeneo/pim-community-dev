##
## Target used run command related on reference entity bounded context
##

.PHONY: asset-manager-coupling
asset-manager-coupling: vendor
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/AssetManager/tests/back/.php_cd.php src/Akeneo/AssetManager/back

.PHONY: asset-manager-phpspec
asset-manager-phpspec: vendor
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/AssetManager/tests/back/phpspec.yml.dist --no-interaction --ansi
