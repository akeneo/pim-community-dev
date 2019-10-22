.PHONY: up-saas-like
up-saas-like:
	$(DOCKER_COMPOSE) --project-name pim-saas-like --file docker-compose.saas-like.yml up --detach --remove-orphan

.PHONY: pim-saas-like
pim-saas-like:
	$(DOCKER_COMPOSE) --project-name pim-saas-like exec fpm bin/console pim:installer:db
	$(DOCKER_COMPOSE) --project-name pim-saas-like exec fpm bin/console pim:user:create --admin -n -- admin admin test@example.com John Doe en_US

.PHONY: down-saas-like
down-saas-like:
	$(DOCKER_COMPOSE) --project-name pim-saas-like --file docker-compose.saas-like.yml down
