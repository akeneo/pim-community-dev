.PHONY: table-attribute-coupling-back
table-attribute-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect src/Akeneo/Pim/TableAttribute/back/ --config-file src/Akeneo/Pim/TableAttribute/tests/back/.php_cd.php
