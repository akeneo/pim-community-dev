RELEASE ?= latest

.PHONY: buildPush
buildPush: build push
	@echo "Done"

build:
	@echo "Build"
	docker build -t eu.gcr.io/akeneo-ci/php-clear-uptime-check:${RELEASE} .

push:
	@echo "Push"
	docker push eu.gcr.io/akeneo-ci/php-clear-uptime-check:${RELEASE}