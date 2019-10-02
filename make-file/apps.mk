define yarn_run
    $(DOCKER_COMPOSE) run -u node --rm node bash -c 'export PATH=$$PWD/node_modules/.bin:$$PATH; yarn run --cwd=src/Akeneo/Apps/front/ $(1)'
endef

# Tests

apps-front-tests:
	$(call yarn_run,jest)

apps-front-codestyle-check:
	$(call yarn_run,tslint)
	$(call yarn_run,prettier --check)

# Development

apps-front-tests-watch:
	$(call yarn_run,jest --watchAll)

apps-front-codestyle-fix:
	$(call yarn_run,tslint --fix)
	$(call yarn_run,prettier)