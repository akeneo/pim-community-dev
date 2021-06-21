DOCKER_COMPOSE_API = docker-compose  --file docker-compose.yml --file src/Akeneo/FreeTrial/akeneo-connect-api/docker-compose.yml

.PHONY: api-up
api-up:
	${DOCKER_COMPOSE_API} up --build --detach --remove-orphans akeneo-connect-api

.PHONY: api-restart
api-restart:
	${DOCKER_COMPOSE_API} restart akeneo-connect-api

.PHONY: api-stop
api-stop:
	${DOCKER_COMPOSE_API} stop akeneo-connect-api

.PHONY: api-logs
api-logs:
	${DOCKER_COMPOSE_API} logs -f akeneo-connect-api

