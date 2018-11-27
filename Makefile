### Coupling detection
.PHONY: structure-coupling
structure-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Structure/.php_cd.php src/Akeneo/Pim/Structure

.PHONY: user-management-coupling
user-management-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/UserManagement/.php_cd.php src/Akeneo/UserManagement

.PHONY: channel-coupling
channel-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Channel/.php_cd.php src/Akeneo/Channel

.PHONY: enrichment-coupling
enrichment-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Enrichment/.php_cd.php src/Akeneo/Pim/Enrichment

.PHONY: coupling
coupling: structure-coupling user-management-coupling channel-coupling
