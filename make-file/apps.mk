_APPS_YARN_RUN = $(YARN_EXEC) run --cwd=src/Akeneo/Apps/front/

# Tests
# Back
apps-coupling:
	$(PHP_RUN) vendor/bin/php-coupling-detector detect --config-file=src/Akeneo/Apps/back/tests/.php_cd.php src/Akeneo/Apps/back

apps-back-phpspec:
	$(PHP_RUN) vendor/bin/phpspec run src/Akeneo/Apps/back/tests/Unit/spec/

apps-back-acceptance:
	$(PHP_RUN) vendor/bin/behat --strict --config src/Akeneo/Apps/back/tests/Acceptance/behat.yml

apps-back-integration:
    $(PHP_RUN) vendor/bin/phpunit -c phpunit.xml.dist --testsuite=Akeneo_Apps_Integration

# Front
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
