##
## Target used run command related on reference entity bounded context
##

.PHONY: asset-manager-coupling-back
asset-manager-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/AssetManager/tests/back/.php_cd.php src/Akeneo/AssetManager/back

.PHONY: asset-manager-lint-back
asset-manager-lint-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/AssetManager/tests/back/phpstan.neon.dist

.PHONY: asset-manager-unit-back
asset-manager-unit-back:
ifeq ($(CI),1)
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/AssetManager/tests/back/phpspec.yml.dist --format=junit > var/tests/phpspec/asset-manager.xml
else
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/AssetManager/tests/back/phpspec.yml.dist $(O)
endif

.PHONY: asset-manager-acceptance-back
asset-manager-acceptance-back: var/tests/behat/asset-manager
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/AssetManager/tests/back/behat.yml.dist --format pim --out var/tests/behat/asset-manager --format progress --out std --colors

.PHONY: asset-manager-acceptance-front
asset-manager-acceptance-front:
	$(YARN_RUN) acceptance-am

.PHONY: asset-manager-integration-back
asset-manager-integration-back: var/tests/phpunit
ifeq ($(CI),1)
	vendor/akeneo/pim-community-dev/.circleci/run_phpunit.sh src/Akeneo/AssetManager/tests/back vendor/akeneo/pim-community-dev/.circleci/find_phpunit.php AssetFamily_Integration_Test
else
	${PHP_RUN} vendor/bin/phpunit -c src/Akeneo/AssetManager/tests/back --testsuite AssetFamily_Integration_Test $(O)
endif
