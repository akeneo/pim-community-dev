##
## Target used run command related on reference entity bounded context
##

.PHONY: reference-entity-coupling-back
reference-entity-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/ReferenceEntity/tests/back/.php_cd.php src/Akeneo/ReferenceEntity/back

.PHONY: reference-entity-lint-back
reference-entity-lint-back:
	$(PHP_RUN) vendor/bin/phpstan analyse --configuration src/Akeneo/ReferenceEntity/tests/back/phpstan.neon.dist

.PHONY: reference-entity-unit-back
reference-entity-unit-back: var/tests/phpspec
ifeq ($(CI),1)
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/ReferenceEntity/tests/back/phpspec.yml.dist --format=junit > var/tests/phpspec/reference-entity.xml
else
	$(PHP_RUN) vendor/bin/phpspec run -c src/Akeneo/ReferenceEntity/tests/back/phpspec.yml.dist $(O)
endif

.PHONY: reference-entity-acceptance-back
reference-entity-acceptance-back:
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/ReferenceEntity/tests/back/behat.yml.dist --format pim --out var/tests/behat/reference-entity --format progress --out std --colors

.PHONY: reference-entity-acceptance-front
reference-entity-acceptance-front:
	$(YARN_RUN) acceptance-re
