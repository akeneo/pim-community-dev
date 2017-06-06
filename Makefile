# Platform detection
ifeq ($(OS),Windows_NT)
    PLATFORM := Windows
else
    PLATFORM := $(shell uname -s)
endif

APP=docker-compose exec --user docker -T app
CONSOLE=$(APP) /usr/bin/php app/console

.PHONY: help install pim-install asset-install start stop composer db-create clear-all clean

help:           ## Show this help
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//'

install:        ## Setup the project using Docker and docker-compose
install: start composer clear-all pim-install asset-install

pim-install:    # Install the PIM
	$(CONSOLE) pim:install --env=prod --force

asset-install:  # Install the assets
	$(CONSOLE) oro:requirejs:generate-config --env=prod
	$(CONSOLE) pim:install:assets --env=prod

start:          ## Start the Docker containers
	docker-compose up -d

stop:           ## Stop the Docker containers and remove the volumes
	docker-compose down -v

composer:       ## Launch Composer
	$(APP) composer $(filter-out $@,$(MAKECMDGOALS))

composer-install:       # Install the project PHP dependencies
	$(APP) composer install -o

db-create:      # Create the database and load the fixtures in it
	$(CONSOLE) pim:installer:db

clear-cache:    # Clear the application cache in development
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

phpspec:        ## Launch Phpspec tests
	$(APP) ./bin/phpspec $(filter-out $@,$(MAKECMDGOALS))

console:        ## Launch Console
	$(CONSOLE) $(filter-out $@,$(MAKECMDGOALS))

clean:          ## Removes all generated files
	- @make clear-all
	$(APP) rm -rf vendor
%:
	@:
