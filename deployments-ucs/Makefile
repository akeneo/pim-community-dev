# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

# Force bash compatibility (Instead of user default shell)
SHELL := /bin/bash

USE_EDITION_FLAG ?= false
ENV_NAME ?= dev
TYPE ?= srnt
TYPE_LONG ?= serenity
PRODUCT_REFERENCE_TYPE ?= $(TYPE_LONG)_instance
PRODUCT_REFERENCE_CODE ?= $(TYPE_LONG)_$(ENV_NAME)

GOOGLE_PROJECT_ID ?= akecld-prd-pim-saas-$(ENV_NAME)
GOOGLE_PROJECT_ID_SHARED ?= akecld-prd-pim-saas-shared
FIRESTORE_PROJECT_ID ?= akecld-prd-pim-fire-eur-$(ENV_NAME)
GOOGLE_CLUSTER_REGION ?= europe-west1
GOOGLE_CLUSTER_ZONE ?= europe-west1-b
GOOGLE_COMPUTE_ZONE ?= $(GOOGLE_CLUSTER_ZONE)
GOOGLE_CLUSTER_NAME ?= akecld-prd-pim-saas-dev-europe-west1
GOOGLE_MANAGED_ZONE_DNS ?= ci.pim.akeneo.cloud
GOOGLE_MANAGED_ZONE_NAME ?= ci-pim-akeneo-cloud
STORAGE_LOCATION ?= EU

TENANT_NAME_PREFIX ?= ucs
TENANT_NAME ?=  $(TENANT_NAME_PREFIX)-$(IMAGE_TAG_SHORTED)
TENANT_ID ?= $(TYPE)-$(TENANT_NAME)
FQDN ?= $(TENANT_NAME).$(GOOGLE_MANAGED_ZONE_DNS)
TENANT_CONTEXT_COLLECTION_NAME ?= tenant_contexts
WORKLOAD_IDENTITY_KSA ?= $(TENANT_NAME)-ksa-workload-identity

PRODUCT_REFERENCE_SIZE ?= S
PRODUCT_REFERENCE_SIZE_INDEX :=
ifeq ($(PRODUCT_REFERENCE_SIZE),S)
	PRODUCT_REFERENCE_SIZE_INDEX := 0
endif
ifeq ($(PRODUCT_REFERENCE_SIZE),M)
	PRODUCT_REFERENCE_SIZE_INDEX := 1
endif
ifeq ($(PRODUCT_REFERENCE_SIZE),L)
	PRODUCT_REFERENCE_SIZE_INDEX := 2
endif
ifeq ($(PRODUCT_REFERENCE_SIZE),XL)
	PRODUCT_REFERENCE_SIZE_INDEX := 3
endif
ifeq ($(PRODUCT_REFERENCE_SIZE_INDEX),)
	XL_PRODUCT_REFERENCE_SIZE_INDEX := $(shell echo $$(echo $(PRODUCT_REFERENCE_SIZE)) | sed 's/XL//' )
	PRODUCT_REFERENCE_SIZE_INDEX := $(shell echo $$(( $(XL_PRODUCT_REFERENCE_SIZE_INDEX) + 2)) )
endif

IMAGE_TAG ?= latest
PIM_NAMESPACE ?= pim-saas
PORTAL_PROJECT_CODE ?= NOT_ON_PAPO_$(TENANT_ID)
DEPLOYMENT_DIR ?= $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
PIM_ROOT_DIR ?= $(realpath $(DEPLOYMENT_DIR)/..)
DEPLOYMENTS_INSTANCES_DIR ?= $(DEPLOYMENT_DIR)/instances
TENANT_DIR := $(DEPLOYMENTS_INSTANCES_DIR)/$(TENANT_ID)
MYSQL_DISK_SIZE ?= 10
MYSQL_DISK_DESCRIPTION ?=
MYSQL_SOURCE_SNAPSHOT ?=
MYSQL_DSIK_STORAGE_CLASS ?= ssd-retain-csi
MYSQL_DSIK_NAME ?= projects/$(GOOGLE_PROJECT_ID)/zones/$(GOOGLE_CLUSTER_ZONE)/disks/$(TENANT_ID)-mysql
# MYSQL_DSIK_NAME ?= projects/$(GOOGLE_PROJECT_ID)/zones/$(GOOGLE_CLUSTER_ZONE)/disks/$(TENANT_NAME)-mysql ???
MYSQL_USER_PASSWORD := $(shell bash -c 'echo $$(pwgen -sB 22)')
MYSQL_ROOT_PASSWORD := $(shell bash -c 'echo $$(pwgen -sB 22)')
MONITORING_AUTHENTICATION_TOKEN := $(shell bash -c 'echo $$(pwgen -sB 32)')
PIM_SECRET := $(shell bash -c 'echo $$(pwgen -sB 32)')

MAILGUN_DOMAIN ?= mg.cloud.akeneo.com
MAILGUN_LOGIN ?= $(TENANT_NAME)-$(GOOGLE_PROJECT_ID)@$(MAILGUN_DOMAIN)
MAILGUN_PASSWORD := $(shell bash -c 'echo $$(pwgen -sB 16)')
MAILGUN_HOST ?= smtp.mailgun.org
MAILGUN_PORT ?= 2525

PIM_ADMIN_USER_LOGIN = pim-ci
PIM_ADMIN_USER_FIRSTNAME = test1
PIM_ADMIN_USER_LASTNAME = test2
PIM_ADMIN_USER_EMAIL = pim-ci@akeneo.fr
PIM_ADMIN_USER_PASSWORD := $(shell bash -c 'echo $$(pwgen -sB 22)')

HEAP_SIZE_UNIT ?= m
MEMORY_UNIT ?= Mi
CPU_UNIT ?= m
INNO_MEMORY_UNIT ?= M
DEFAULT_ES_CLIENT_HEAP_SIZE := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.client.heapSize') | sed 's/$(HEAP_SIZE_UNIT)//' )
ES_CLIENT_HEAP_SIZE := $(shell echo $$(( $(DEFAULT_ES_CLIENT_HEAP_SIZE) + $(DEFAULT_ES_CLIENT_HEAP_SIZE) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(HEAP_SIZE_UNIT) )
DEFAULT_ES_CLIENT_LIMITS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.client.resources.limits.memory') | sed 's/$(MEMORY_UNIT)//' )
ES_CLIENT_LIMITS_MEMORY := $(shell echo $$(( $(DEFAULT_ES_CLIENT_LIMITS_MEMORY) + $(DEFAULT_ES_CLIENT_LIMITS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )
DEFAULT_ES_CLIENT_REQUESTS_CPU := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.client.resources.requests.cpu') | sed 's/$(CPU_UNIT)//' )
ES_CLIENT_REQUESTS_CPU := $(shell echo $$(( $(DEFAULT_ES_CLIENT_REQUESTS_CPU) + $(DEFAULT_ES_CLIENT_REQUESTS_CPU) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(CPU_UNIT) )
DEFAULT_ES_CLIENT_REQUESTS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.client.resources.requests.memory') | sed 's/$(MEMORY_UNIT)//' )
ES_CLIENT_REQUESTS_MEMORY := $(shell echo $$(( $(DEFAULT_ES_CLIENT_REQUESTS_MEMORY) + $(DEFAULT_ES_CLIENT_REQUESTS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )
DEFAULT_ES_MASTER_HEAP_SIZE := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.master.heapSize') | sed 's/$(HEAP_SIZE_UNIT)//' )
ES_MASTER_HEAP_SIZE := $(shell echo $$(( $(DEFAULT_ES_MASTER_HEAP_SIZE) + $(DEFAULT_ES_MASTER_HEAP_SIZE) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(HEAP_SIZE_UNIT) )
DEFAULT_ES_MASTER_LIMITS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.master.resources.limits.memory') | sed 's/$(MEMORY_UNIT)//' )
ES_MASTER_LIMITS_MEMORY := $(shell echo $$(( $(DEFAULT_ES_MASTER_LIMITS_MEMORY) + $(DEFAULT_ES_MASTER_LIMITS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )
DEFAULT_ES_MASTER_REQUESTS_CPU := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.master.resources.requests.cpu') | sed 's/$(CPU_UNIT)//' )
ES_MASTER_REQUESTS_CPU := $(shell echo $$(( $(DEFAULT_ES_MASTER_REQUESTS_CPU) + $(DEFAULT_ES_MASTER_REQUESTS_CPU) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(CPU_UNIT) )
DEFAULT_ES_MASTER_REQUESTS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.master.resources.requests.memory') | sed 's/$(MEMORY_UNIT)//' )
ES_MASTER_REQUESTS_MEMORY := $(shell echo $$(( $(DEFAULT_ES_MASTER_REQUESTS_MEMORY) + $(DEFAULT_ES_MASTER_REQUESTS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )
DEFAULT_ES_DATA_HEAP_SIZE := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.data.heapSize') | sed 's/$(HEAP_SIZE_UNIT)//' )
ES_DATA_HEAP_SIZE := $(shell echo $$(( $(DEFAULT_ES_DATA_HEAP_SIZE) + $(DEFAULT_ES_DATA_HEAP_SIZE) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(HEAP_SIZE_UNIT) )
DEFAULT_ES_DATA_LIMITS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.data.resources.limits.memory') | sed 's/$(MEMORY_UNIT)//' )
ES_DATA_LIMITS_MEMORY := $(shell echo $$(( $(DEFAULT_ES_DATA_LIMITS_MEMORY) + $(DEFAULT_ES_DATA_LIMITS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )
DEFAULT_ES_DATA_REQUESTS_CPU := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.data.resources.requests.cpu') | sed 's/$(CPU_UNIT)//' )
ES_DATA_REQUESTS_CPU := $(shell echo $$(( $(DEFAULT_ES_DATA_REQUESTS_CPU) + $(DEFAULT_ES_DATA_REQUESTS_CPU) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(CPU_UNIT) )
DEFAULT_ES_DATA_REQUESTS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'elasticsearch.data.resources.requests.memory') | sed 's/$(MEMORY_UNIT)//' )
ES_DATA_REQUESTS_MEMORY := $(shell echo $$(( $(DEFAULT_ES_DATA_REQUESTS_MEMORY) + $(DEFAULT_ES_DATA_REQUESTS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )
DEFAULT_MYSQL_INNODB_BUFFER_POOL_SIZE := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'mysql.mysql.innodbBufferPoolSize') | sed 's/$(INNO_MEMORY_UNIT)//' )
MYSQL_INNODB_BUFFER_POOL_SIZE := $(shell echo $$(( $(DEFAULT_MYSQL_INNODB_BUFFER_POOL_SIZE) + 1024 * $(PRODUCT_REFERENCE_SIZE_INDEX) ))$(INNO_MEMORY_UNIT) )
DEFAULT_MYSQL_LIMITS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'mysql.mysql.resources.limits.memory') | sed 's/$(MEMORY_UNIT)//' )
MYSQL_LIMITS_MEMORY := $(shell echo $$(( $(DEFAULT_MYSQL_LIMITS_MEMORY) + 1024 * $(PRODUCT_REFERENCE_SIZE_INDEX) * 130 / 100 ))$(MEMORY_UNIT) )
DEFAULT_MYSQL_REQUESTS_CPU := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'mysql.mysql.resources.requests.cpu') | sed 's/$(CPU_UNIT)//' )
MYSQL_REQUESTS_CPU := $(shell echo $$(( $(DEFAULT_MYSQL_REQUESTS_CPU) + $(DEFAULT_MYSQL_REQUESTS_CPU) * $(PRODUCT_REFERENCE_SIZE_INDEX) * 130 / 100 / 2 ))$(CPU_UNIT) )
DEFAULT_MYSQL_REQUESTS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'mysql.mysql.resources.requests.memory') | sed 's/$(MEMORY_UNIT)//' )
MYSQL_REQUESTS_MEMORY := $(shell echo $$(( $(DEFAULT_MYSQL_REQUESTS_MEMORY) + 1024 * $(PRODUCT_REFERENCE_SIZE_INDEX) * 130 / 100 ))$(MEMORY_UNIT) )
DEFAULT_MEMCACHED_LIMITS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'memcached.resources.limits.memory') | sed 's/$(MEMORY_UNIT)//' )
MEMCACHED_LIMITS_MEMORY := $(shell echo $$(( $(DEFAULT_MEMCACHED_LIMITS_MEMORY) + $(DEFAULT_MEMCACHED_LIMITS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )
DEFAULT_MEMCACHED_REQUESTS_CPU := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'memcached.resources.requests.cpu') | sed 's/$(CPU_UNIT)//' )
MEMCACHED_REQUESTS_CPU := $(shell echo $$(( $(DEFAULT_MEMCACHED_REQUESTS_CPU) + $(DEFAULT_MEMCACHED_REQUESTS_CPU) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(CPU_UNIT) )
DEFAULT_MEMCACHED_REQUESTS_MEMORY := $(shell echo $$(yq r "$(DEPLOYMENT_DIR)/tenant/values.yaml" 'memcached.resources.requests.memory') | sed 's/$(MEMORY_UNIT)//' )
MEMCACHED_REQUESTS_MEMORY := $(shell echo $$(( $(DEFAULT_MEMCACHED_REQUESTS_MEMORY) + $(DEFAULT_MEMCACHED_REQUESTS_MEMORY) * $(PRODUCT_REFERENCE_SIZE_INDEX) / 2 ))$(MEMORY_UNIT) )

.DEFAULT_GOAL := help

.PHONY: help
help: #Doc: display this help
	@echo "$$(grep -hE '^\S+:.*#Doc:' $(MAKEFILE_LIST) | sed -e 's/:.*#Doc:\s*/:/' -e 's/^\(.\+\):\(.*\)/\1:-\ \2/' | column -c2 -t -s :)"


###################
### UCS 				###
###################
## Build
.PHONY: pim-image-prod
pim-image-prod: #Doc: build docker image for pim-enterprise-dev with the prod tag
	echo "${RELEASE_NAME}" > "$(PIM_ROOT_DIR)/version.txt"
	DOCKER_BUILDKIT=1 docker build --no-cache --progress=plain --pull --tag $(GOOGLE_CLUSTER_REGION)-docker.pkg.dev/$(GOOGLE_PROJECT_ID_SHARED)/dev/pim-enterprise-dev:${IMAGE_TAG} --target prod --build-arg COMPOSER_AUTH='${COMPOSER_AUTH}' -f $(PIM_ROOT_DIR)/Dockerfile ${PIM_ROOT_DIR}

.PHONY: push-pim-image-prod
push-pim-image-prod: #Doc: push docker image to docker hub
	docker push $(GOOGLE_CLUSTER_REGION)-docker.pkg.dev/$(GOOGLE_PROJECT_ID_SHARED)/dev/pim-enterprise-dev --all-tags

## Deploy tenants
.PHONY: deploy_tenant
deploy_tenant: generate_argocd_application deploy_tenant_argocd deploy_tenant_go
	@echo "#######################################################################################"
	@echo ""
	@echo "This environment is available at https://$(FQDN) :)"
	@echo ""
	@echo "K9s direct access command line for the pim: k9s -n $(PIM_NAMESPACE) -c pods"
	@echo "K9s direct access command line for the tenant: k9s -n $(TENANT_ID) -c pods"
	@echo ""
	@echo "#######################################################################################"
	PIM_USER=$(shell yq r "$(TENANT_DIR)/tenant-application.yaml" 'spec.source.helm.parameters.(name==pim.defaultAdminUser.login).value'); \
	PIM_EMAIL=$(shell yq r "$(TENANT_DIR)/tenant-application.yaml" 'spec.source.helm.parameters.(name==pim.defaultAdminUser.email).value'); \
	PIM_PASSWORD=$(shell yq r "$(TENANT_DIR)/tenant-application.yaml" 'spec.source.helm.parameters.(name==pim.defaultAdminUser.password).value')

$(TENANT_DIR):
	mkdir -p $(TENANT_DIR)

.PHONY: generate_argocd_application
generate_argocd_application: $(TENANT_DIR)
	@echo "=========================================================="
	@echo "Deploy pim namespace : $(PIM_NAMESPACE)"
	@echo "Deploy tenant namespace : $(TENANT_ID)"
	@echo " - with instance name prefix : $(TENANT_NAME_PREFIX)"
	@echo " - with instance name : $(TENANT_NAME)"
	@echo " - with image tag : $(IMAGE_TAG)"
	@echo " - on cluster : $(GOOGLE_CLUSTER_NAME)"
	@echo " - URL : $(FQDN)"
	@echo "=========================================================="
	cat $(DEPLOYMENT_DIR)/config/tenant-application-templates.yaml > $(TENANT_DIR)/tenant-application.yaml.tmp
	USE_EDITION_FLAG=$(USE_EDITION_FLAG) \
	IMAGE_TAG=$(IMAGE_TAG) \
	TYPE=$(TYPE) \
	TENANT_NAME=$(TENANT_NAME) \
	TENANT_ID=$(TENANT_ID) \
	FQDN=$(FQDN) \
	PIM_NAMESPACE=${PIM_NAMESPACE} \
	GOOGLE_MANAGED_ZONE_DNS=$(GOOGLE_MANAGED_ZONE_DNS) \
	PRODUCT_REFERENCE_CODE=$(PRODUCT_REFERENCE_CODE) \
	PRODUCT_REFERENCE_TYPE=$(PRODUCT_REFERENCE_TYPE) \
	PRODUCT_REFERENCE_SIZE=$(PRODUCT_REFERENCE_SIZE) \
	PORTAL_PROJECT_CODE=$(PORTAL_PROJECT_CODE) \
	FIRESTORE_PROJECT_ID=$(FIRESTORE_PROJECT_ID) \
	GOOGLE_PROJECT_ID=$(GOOGLE_PROJECT_ID) \
	GOOGLE_CLUSTER_ZONE=$(GOOGLE_CLUSTER_ZONE) \
	TENANT_CONTEXT_COLLECTION_NAME=$(TENANT_CONTEXT_COLLECTION_NAME) \
	STORAGE_LOCATION=$(STORAGE_LOCATION) \
	MONITORING_AUTHENTICATION_TOKEN=$(MONITORING_AUTHENTICATION_TOKEN) \
	MONITORING_AUTHENTICATION_TOKEN_TEST=$(MONITORING_AUTHENTICATION_TOKEN_TEST) \
	PIM_ADMIN_USER_LOGIN=$(PIM_ADMIN_USER_LOGIN) \
	PIM_ADMIN_USER_FIRSTNAME=$(PIM_ADMIN_USER_FIRSTNAME) \
	PIM_ADMIN_USER_LASTNAME=$(PIM_ADMIN_USER_LASTNAME) \
	PIM_ADMIN_USER_EMAIL=$(PIM_ADMIN_USER_EMAIL) \
	PIM_ADMIN_USER_PASSWORD=$(PIM_ADMIN_USER_PASSWORD) \
	PIM_SECRET=$(PIM_SECRET) \
	MAILGUN_LOGIN=$(MAILGUN_LOGIN) \
	MAILGUN_PASSWORD=$(MAILGUN_PASSWORD) \
	MAILGUN_HOST=$(MAILGUN_HOST) \
	MAILGUN_DOMAIN=$(MAILGUN_DOMAIN) \
	MAILGUN_PORT=$(MAILGUN_PORT) \
	MAILGUN_API_KEY=${MAILGUN_API_KEY} \
	MYSQL_USER_PASSWORD=$(MYSQL_USER_PASSWORD) \
	MYSQL_ROOT_PASSWORD=$(MYSQL_ROOT_PASSWORD) \
	MYSQL_DISK_SIZE=$(MYSQL_DISK_SIZE) \
	MYSQL_DSIK_NAME=$(MYSQL_DSIK_NAME) \
	MYSQL_DSIK_STORAGE_CLASS=$(MYSQL_DSIK_STORAGE_CLASS) \
	MYSQL_INNODB_BUFFER_POOL_SIZE=$(MYSQL_INNODB_BUFFER_POOL_SIZE) \
	MYSQL_LIMITS_MEMORY=$(MYSQL_LIMITS_MEMORY) \
	MYSQL_REQUESTS_CPU=$(MYSQL_REQUESTS_CPU) \
	MYSQL_REQUESTS_MEMORY=$(MYSQL_REQUESTS_MEMORY) \
	ES_CLIENT_HEAP_SIZE=$(ES_CLIENT_HEAP_SIZE) \
	ES_CLIENT_LIMITS_MEMORY=$(ES_CLIENT_LIMITS_MEMORY) \
	ES_CLIENT_REQUESTS_CPU=$(ES_CLIENT_REQUESTS_CPU) \
	ES_CLIENT_REQUESTS_MEMORY=$(ES_CLIENT_REQUESTS_MEMORY) \
	ES_MASTER_HEAP_SIZE=$(ES_MASTER_HEAP_SIZE) \
	ES_MASTER_LIMITS_MEMORY=$(ES_MASTER_LIMITS_MEMORY) \
	ES_MASTER_REQUESTS_CPU=$(ES_MASTER_REQUESTS_CPU) \
	ES_MASTER_REQUESTS_MEMORY=$(ES_MASTER_REQUESTS_MEMORY) \
	ES_DATA_HEAP_SIZE=$(ES_DATA_HEAP_SIZE) \
	ES_DATA_LIMITS_MEMORY=$(ES_DATA_LIMITS_MEMORY) \
	ES_DATA_REQUESTS_CPU=$(ES_DATA_REQUESTS_CPU) \
	ES_DATA_REQUESTS_MEMORY=$(ES_DATA_REQUESTS_MEMORY) \
	MEMCACHED_LIMITS_MEMORY=$(MEMCACHED_LIMITS_MEMORY) \
	MEMCACHED_REQUESTS_CPU=$(MEMCACHED_REQUESTS_CPU) \
	MEMCACHED_REQUESTS_MEMORY=$(MEMCACHED_REQUESTS_MEMORY) \
	WORKLOAD_IDENTITY_KSA=${WORKLOAD_IDENTITY_KSA} \
	envsubst < $(TENANT_DIR)/tenant-application.yaml.tmp > $(TENANT_DIR)/tenant-application.yaml ;\
	rm -rf $(TENANT_DIR)/tenant-application.yaml.tmp
	cat $(TENANT_DIR)/tenant-application.yaml

.PHONY: deploy_tenant_argocd
deploy_tenant_argocd:
	kubectl apply -f $(TENANT_DIR)/tenant-application.yaml -n argocd

.PHONY: deploy_tenant_go
deploy_tenant_go:
	cd $(DEPLOYMENT_DIR)/timmy && go run timmy-create.go $(TENANT_ID) $(TENANT_NAME) $(MYSQL_USER_PASSWORD) $(MAILGUN_PASSWORD) $(PIM_SECRET) $(TENANT_CONTEXT_COLLECTION_NAME) $(PRODUCT_REFERENCE_TYPE)

## Cleanup tenants
.PHONY: delete_tenant
delete_tenant:
	kubectl delete app $(TENANT_ID) -n argocd
	kubectl delete ns $(TENANT_ID)
	cd $(DEPLOYMENT_DIR)/timmy && go run timmy-remove.go $(TENANT_ID) $(TENANT_CONTEXT_COLLECTION_NAME)
