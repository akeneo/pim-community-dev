DOCKER ?= false
PHP_EXEC :=php

ifeq ($(DOCKER),true)
	PHP_EXEC=docker-compose exec -u docker fpm php
endif

### Coupling detection
.PHONY: structure-coupling
structure-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Structure/.php_cd.php src/Akeneo/Pim/Structure

.PHONY: user-management-coupling
user-management-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement

.PHONY: channel-coupling
channel-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Channel/.php_cd.php src/Akeneo/Channel

.PHONY: enrichment-coupling
enrichment-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/.php_cd.php src/Akeneo/Pim/Enrichment

.PHONY: coupling
coupling: structure-coupling user-management-coupling channel-coupling
