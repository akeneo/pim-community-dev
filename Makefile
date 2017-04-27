ifeq ($(OS), Windows_NT)
	APP=docker-compose exec -T app
else
	APP=docker-compose exec --user docker -T app
endif

CONSOLE=$(APP) /usr/bin/php app/console

.PHONY: help install pim-install asset-install start stop composer db-create db-update clear-cache clear-all clean

help:           ## Show this help
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

install:        ## [start composer db-create pim-install clear-all asset-install] Setup the project using Docker and docker-compose
install: start composer clear-all pim-install asset-install

pim-install:    ## Install the PIM
	$(CONSOLE) pim:install --env=prod --force

asset-install:  ## Install the assets
	$(CONSOLE) oro:requirejs:generate-config --env=prod
	$(CONSOLE) pim:install:assets --env=prod

start:          ## Start the Docker containers
	docker-compose up -d

stop:           ## Stop the Docker containers and remove the volumes
	docker-compose down -v

composer:       ## Install the project PHP dependencies
	$(APP) composer install -o

db-create:      ## Create the database and load the fixtures in it
	$(CONSOLE) pim:installer:db

db-update:      ## Update the database structure according to the last changes
	$(CONSOLE) doctrine:schema:update --force

clear-cache:    ## Clear the application cache in development
	$(CONSOLE) cache:clear

clear-all:      ## Deeply clean the application (remove all the cache, the logs, the sessions and the built assets)
	$(APP) rm -fr app/archive/*
	$(APP) rm -fr app/cache/*
	$(APP) rm -fr app/file_storage/*
	$(APP) rm -rf app/logs/*
	$(APP) rm -rf app/sessions/*
	$(APP) rm -rf web/bundles/*
	$(APP) rm -rf web/css/*
	$(APP) rm -rf web/js/*
	$(APP) rm -rf web/media/*
	$(APP) rm -rf supervisord.log supervisord.pid .tmp

clean:          ## Removes all generated files
	- @make clear-all
	$(APP) rm -rf vendor
