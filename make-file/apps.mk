_APPS_YARN_RUN = $(YARN_EXEC) run --cwd=src/Akeneo/Apps/front/

# Tests Back

apps-coupling-back:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Apps/back/tests/.php_cd.php src/Akeneo/Apps/back

apps-unit-back:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Apps/back/tests/Unit/spec/

apps-acceptance-back: var/tests/behat/apps
	$(PHP_RUN) vendor/bin/behat --config src/Akeneo/Apps/back/tests/Acceptance/behat.yml --format pim --out var/tests/behat/apps --format progress --out std --colors

apps-integration-back:
	$(PHP_RUN) vendor/bin/phpunit -c phpunit.xml.dist --testsuite=Akeneo_Apps_Integration

apps-back:
	make apps-coupling-back
	make apps-unit-back
	make apps-integration-back
	make apps-acceptance-back

# Tests Front

apps-front-tests:
	$(_APPS_YARN_RUN) jest

apps-front-lint:
	$(_APPS_YARN_RUN) eslint
	$(_APPS_YARN_RUN) prettier --check

# Development

apps-front-tests-watch:
	$(_APPS_YARN_RUN) jest --watchAll --coverage

apps-front-lint-fix:
	$(_APPS_YARN_RUN) eslint --fix
	$(_APPS_YARN_RUN) prettier --write
