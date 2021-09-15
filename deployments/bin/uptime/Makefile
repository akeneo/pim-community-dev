RELEASE ?= latest

.PHONY: buildPush
buildPush: build push
	@echo "Done"

build:
	@echo "Build"
	docker build -t eu.gcr.io/akeneo-ci/php-deployment:${RELEASE} .

push:
	@echo "Push"
	docker push eu.gcr.io/akeneo-ci/php-deployment:${RELEASE}

.PHONY: install
install:
	composer install
	

.PHONY: deployment-uptime-clear
deployment-uptime-clear:
	./deployment deployment:uptime:clear

.PHONY: deployment-uptime-get
deployment-uptime-get:
	./deployment deployment:uptime:get
