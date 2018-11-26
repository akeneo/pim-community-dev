### Coupling detection
.PHONY: reference-entity-coupling
reference-entity-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/ReferenceEntity/tests/back/.php_cd.php src/Akeneo/ReferenceEntity/back

.PHONY: suggest-data-coupling
suggest-data-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/Automation/SuggestData/tests/back/.php_cd.php src/Akeneo/Pim/Automation/SuggestData

.PHONY: asset-coupling
asset-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Asset/.php_cd.php src/Akeneo/Asset

.PHONY: twa-coupling
twa-coupling:
	vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Pim/WorkOrganization/TeamworkAssistant/.php_cd.php src/Akeneo/Pim/WorkOrganization/TeamworkAssistant

.PHONY: coupling
coupling: twa-coupling asset-coupling suggest-data-coupling reference-entity-coupling
