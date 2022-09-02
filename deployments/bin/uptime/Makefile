RELEASE ?= latest

buildPush: build push
	@echo "Done"

build:
	@echo "Build"
	docker build -t eu.gcr.io/akeneo-ci/php-deployment:${RELEASE} .

push:
	@echo "Push"
	docker push eu.gcr.io/akeneo-ci/php-deployment:${RELEASE}

install:
	docker-compose run --rm composer install

phpstan:
	docker-compose run --rm php php -d memory_limit=-1 ./vendor/phpstan/phpstan/phpstan analyse

test: phpstan
	@echo "Tests done"

deployment-uptime-clear:
	./deployment deployment:uptime:clear

deployment-uptime-get:
	./deployment deployment:uptime:get
