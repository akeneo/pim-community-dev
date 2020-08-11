.PHONY: pim-shared-catalog
pim-shared-catalog:
	APP_ENV=dev $(MAKE) up
	APP_ENV=dev $(MAKE) cache
	$(MAKE) assets
	$(MAKE) css
	$(MAKE) javascript-dev
	docker/wait_docker_up.sh
	APP_ENV=dev O="--catalog shared_catalog_fixtures" $(MAKE) database
	APP_ENV=dev $(PHP_RUN) bin/console akeneo:shared-catalog:fixtures --force
