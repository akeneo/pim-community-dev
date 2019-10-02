define yarn_run
    $(DOCKER_COMPOSE) run -u node --rm node bash -c 'export PATH=$$PWD/node_modules/.bin:$$PATH; yarn run --cwd=src/Akeneo/Apps/front/ $(1)'
endef

apps-front-tests-spec:
	$(call yarn_run,jest)

apps-front-codestyle-check:
	$(call yarn_run,tslint)
	$(call yarn_run,prettier --check)

apps-front-codestyle-fix:
	$(call yarn_run,tslint --fix)
	$(call yarn_run,prettier)