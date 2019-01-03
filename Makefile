DOCKER ?= false
PHP_EXEC :=php

ifeq ($(DOCKER),true)
	PHP_EXEC=docker-compose exec -u docker fpm php
endif

.DEFAULT_GOAL := help

.PHONY: help
help:
	@echo ""
	@echo "Akeneo Pim Community Dev available targets:"
	@echo ""
	@echo "If you want to use docker, use an environment variable DOCKER=true"
	@echo ""
	@grep -E '^.PHONY:.*##.*' $(MAKEFILE_LIST) | cut -c9- | sort | awk 'BEGIN {FS = " ## "}; {printf "%-30s %s\n", $$1, $$2}'

### Coupling detection
.PHONY: structure-coupling ## Run the coupling-detector on Structure
structure-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Structure/.php_cd.php src/Akeneo/Pim/Structure

.PHONY: user-management-coupling ## Run the coupling-detector on User Management
user-management-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement

.PHONY: channel-coupling ## Run the coupling-detector on Channel
channel-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Channel/.php_cd.php src/Akeneo/Channel

.PHONY: enrichment-coupling ## Run the coupling-detector on Enrichment
enrichment-coupling:
	$(PHP_EXEC) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/.php_cd.php src/Akeneo/Pim/Enrichment

.PHONY: coupling ## Run the coupling-detector on Everything
coupling: structure-coupling user-management-coupling channel-coupling enrichment-coupling
