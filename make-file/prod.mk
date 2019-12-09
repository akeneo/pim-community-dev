IMAGE_TAG ?= master

.PHONY: php-image-prod
php-image-prod:
	DOCKER_BUILDKIT=1 docker build --progress=plain --pull --tag eu.gcr.io/akeneo-cloud:${IMAGE_TAG} --target prod .
