# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

# Force bash compatibility (Instead of user default shell)
SHELL := /bin/bash

TYPE ?= srnt
INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?=  $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG_SHORTED)
PFID ?= $(TYPE)-$(INSTANCE_NAME)
TYPE_LONG ?= serenity
ENV_NAME ?= sandbox
PRODUCT_REFERENCE_TYPE ?= $(TYPE_LONG)_instance
PRODUCT_REFERENCE_CODE ?= $(TYPE_LONG)_$(ENV_NAME)
BUCKET ?= serenity-edition-dev
IMAGE_TAG ?= latest

GOOGLE_PROJECT_ID ?= akecld-blackhawk-$(ENV_NAME)
GOOGLE_CLUSTER_REGION ?= europe-west1
GOOGLE_CLUSTER_ZONE ?= europe-west1-b
GOOGLE_CLUSTER_NAME ?= bh-cluster-sandbox
GOOGLE_MANAGED_ZONE_DNS ?= bh.akeneo.ch
GOOGLE_MANAGED_ZONE_NAME ?= bh-akeneo-ch
CLUSTER_DNS_NAME ?= $(GOOGLE_CLUSTER_ZONE)-akecld-blackhawk-$(ENV_NAME).bh.akeneo.ch.

DEPLOYMENT_DIR ?= $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
PIM_SRC_PATH ?= $(realpath $(DEPLOYMENT_DIR)/..)
PIM_ROOT_DIR ?= $(realpath $(DEPLOYMENT_DIR)/..)
DEPLOYMENTS_INSTANCES_DIR ?= $(DEPLOYMENT_DIR)/instances
INSTANCE_DIR := $(DEPLOYMENTS_INSTANCES_DIR)/$(PFID)
MAIN_TF_TEMPLATE ?= $(PRODUCT_REFERENCE_TYPE)
MYSQL_DISK_SIZE ?= 10
MYSQL_DISK_DESCRIPTION ?=
MYSQL_SOURCE_SNAPSHOT ?=
DEPLOYMENT_HASH ?=
BUCKET_PIM_SRC_DIR ?= gcs::https://www.googleapis.com/storage/v1/akecld-blackhawk-sandbox-terraform-modules/$(BUCKET)/$(DEPLOYMENT_HASH)

TF_INPUT_FALSE ?= -input=false
TF_AUTO_APPROVE ?= -auto-approve


# ?
UPGRADE_STEP_2 ?= false
#Vars for exec_in
executor ?= kubectl
migrate ?= no
# ?


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
	echo "${RELEASE_NAME}" > "$(PIM_SRC_PATH)/version.txt"
	DOCKER_BUILDKIT=1 docker build --no-cache --progress=plain --pull --tag eu.gcr.io/$(GOOGLE_PROJECT_ID)/pim-enterprise-dev:${IMAGE_TAG} --tag eu.gcr.io/$(GOOGLE_PROJECT_ID)/pim-enterprise-dev:latest --target prod --build-arg COMPOSER_AUTH='${COMPOSER_AUTH}' -f $(PIM_SRC_PATH)/Dockerfile ${PIM_ROOT_DIR}

.PHONY: push-pim-image-prod
push-pim-image-prod: #Doc: push docker image to docker hub
	docker push eu.gcr.io/$(GOOGLE_PROJECT_ID)/pim-enterprise-dev --all-tags

## Deploy tenants
.PHONY: deploy_tenant
deploy_tenant: generate_deployment_files deploy_tenant_terraform deploy_tenant_helm deploy_tenant_go
	@echo "#######################################################################################"
	@echo ""
	@echo "This environment is available at https://$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS) :)"
	@echo ""
	@echo "K9s direct access command line: k9s -n $(PFID) -c pods"
	@echo ""
	@echo "#######################################################################################"
	PIM_USER=$(shell yq r "$(INSTANCE_DIR)/values.yaml" 'pim.defaultAdminUser.login'); \
	PIM_EMAIL=$(shell yq r "$(INSTANCE_DIR)/values.yaml" 'pim.defaultAdminUser.email'); \
	PIM_PASSWORD=$(shell yq r "$(INSTANCE_DIR)/values.yaml" 'pim.defaultAdminUser.password');

.PHONY: generate_deployment_files
generate_deployment_files: $(INSTANCE_DIR)
	@echo "=========================================================="
	@echo "Deploy namespace : $(PFID)"
	@echo " - with instance name prefix : $(INSTANCE_NAME_PREFIX)"
	@echo " - with image tag : $(IMAGE_TAG)"
	@echo " - on cluster : $(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)"
	@echo " - URL : $(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS)"
	@echo "=========================================================="
	if [ ! -f $(INSTANCE_DIR)/values.yaml ]; then \
		cp $(DEPLOYMENT_DIR)/config/ci-values.yaml $(INSTANCE_DIR)/values.yaml ;\
	fi

	cat $(DEPLOYMENT_DIR)/config/$(MAIN_TF_TEMPLATE).tpl.tf.json > $(INSTANCE_DIR)/$(MAIN_TF_TEMPLATE).tpl.tf.json.tmp

	CLUSTER_DNS_NAME=$(CLUSTER_DNS_NAME) \
	GOOGLE_CLUSTER_ZONE=$(GOOGLE_CLUSTER_ZONE) \
	GOOGLE_CLUSTER_NAME=$(GOOGLE_CLUSTER_NAME) \
	GOOGLE_MANAGED_ZONE_DNS=$(GOOGLE_MANAGED_ZONE_DNS) \
	GOOGLE_MANAGED_ZONE_NAME=$(GOOGLE_MANAGED_ZONE_NAME) \
	GOOGLE_PROJECT_ID=$(GOOGLE_PROJECT_ID) \
	IMAGE_TAG=$(IMAGE_TAG) \
	INSTANCE_NAME=$(INSTANCE_NAME) \
	PFID=$(PFID) \
	BUCKET_PIM_SRC_DIR=$(BUCKET_PIM_SRC_DIR) \
	PIM_SRC_DIR=$(PIM_ROOT_DIR) \
	TYPE=$(TYPE) \
	PRODUCT_REFERENCE_TYPE=$(PRODUCT_REFERENCE_TYPE) \
	PRODUCT_REFERENCE_CODE=$(PRODUCT_REFERENCE_CODE) \
	MYSQL_DISK_SIZE=$(MYSQL_DISK_SIZE) \
	MYSQL_DISK_NAME=$(PFID)-mysql \
	MYSQL_DISK_DESCRIPTION=$(MYSQL_DISK_DESCRIPTION) \
	MYSQL_SOURCE_SNAPSHOT=$(MYSQL_SOURCE_SNAPSHOT) \
	MAILGUN_API_KEY=${MAILGUN_API_KEY} \
	FT_CATALOG_API_BASE_URI=${FT_CATALOG_API_BASE_URI} \
	FT_CATALOG_API_CLIENT_ID=${FT_CATALOG_API_CLIENT_ID} \
	FT_CATALOG_API_PASSWORD=${FT_CATALOG_API_PASSWORD} \
	FT_CATALOG_API_SECRET=${FT_CATALOG_API_SECRET} \
	FT_CATALOG_API_USERNAME=${FT_CATALOG_API_USERNAME} \
	AKENEO_CONNECT_API_CLIENT_SECRET=${AKENEO_CONNECT_API_CLIENT_SECRET} \
	AKENEO_CONNECT_API_CLIENT_PASSWORD=${AKENEO_CONNECT_API_CLIENT_PASSWORD} \
	AKENEO_CONNECT_SAML_ENTITY_ID=${AKENEO_CONNECT_SAML_ENTITY_ID} \
	AKENEO_CONNECT_SAML_CERTIFICATE=${AKENEO_CONNECT_SAML_CERTIFICATE} \
	AKENEO_CONNECT_SAML_SP_CLIENT_ID=${AKENEO_CONNECT_SAML_SP_CLIENT_ID} \
	AKENEO_CONNECT_SAML_SP_CERTIFICATE_BASE64=${AKENEO_CONNECT_SAML_SP_CERTIFICATE_BASE64} \
	AKENEO_CONNECT_SAML_SP_PRIVATE_KEY_BASE64=${AKENEO_CONNECT_SAML_SP_PRIVATE_KEY_BASE64} \
	envsubst < $(INSTANCE_DIR)/$(MAIN_TF_TEMPLATE).tpl.tf.json.tmp > $(INSTANCE_DIR)/main.tf.json ;\
	rm -rf $(INSTANCE_DIR)/$(MAIN_TF_TEMPLATE).tpl.tf.json.tmp

$(INSTANCE_DIR):
	mkdir -p $(INSTANCE_DIR)

.PHONY: deploy_tenant_terraform
deploy_tenant_terraform: $(INSTANCE_DIR)
ifeq ($(UPGRADE_STEP_2),true)
	@echo "We are in the second step of update"
	cd $(INSTANCE_DIR) && STEP='PRE_INIT' INSTANCE_NAME=$(INSTANCE_NAME) bash $(DEPLOYMENT_DIR)/automation/upgrade.sh
	cd $(INSTANCE_DIR) && terraform plan
	cd $(INSTANCE_DIR) && STEP='PRE_APPLY' INSTANCE_NAME=$(INSTANCE_NAME) bash $(DEPLOYMENT_DIR)/automation/upgrade.sh
endif
	cd $(INSTANCE_DIR) && cat main.tf.json
	cd $(INSTANCE_DIR) && terraform init $(TF_INPUT_FALSE) -upgrade
	cd $(INSTANCE_DIR) && terraform plan '-out=upgrades.tfplan' $(TF_INPUT_FALSE) -compact-warnings
	cd $(INSTANCE_DIR) && terraform show -json upgrades.tfplan > ~/upgrades.tfplan.json
	cd $(INSTANCE_DIR) && HELM_DEBUG=true terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE) upgrades.tfplan

.PHONY: deploy_tenant_helm
deploy_tenant_helm:
	yq w -i $(INSTANCE_DIR)/.terraform/modules/pim/deployments-ucs/tenant/Chart.yaml version 0.0.0-$(IMAGE_TAG)
	yq w -i $(INSTANCE_DIR)/.terraform/modules/pim/deployments-ucs/tenant/Chart.yaml appVersion $(IMAGE_TAG)
	helm3 repo add akeneo-charts gs://akeneo-charts/ 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 repo update 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 dependencies update $(INSTANCE_DIR)/.terraform/modules/pim/deployments-ucs/tenant/ 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	export KUBECONFIG=$(INSTANCE_DIR)/.kubeconfig
	helm3 upgrade --debug --atomic --cleanup-on-fail --history-max 5 --install --wait --timeout 10m --create-namespace --namespace $(PFID) $(PFID) $(INSTANCE_DIR)/.terraform/modules/pim/deployments-ucs/tenant/ -f $(INSTANCE_DIR)/tf-helm-pim-values.yaml -f $(INSTANCE_DIR)/values.yaml

.PHONY: deploy_tenant_go
deploy_tenant_go:
	MYSQL_PASSWORD=$(shell yq r "$(INSTANCE_DIR)/tf-helm-pim-values.yaml" 'mysql.mysql.userPassword'); \
	MAILGUN_PASSWORD=$(shell yq r "$(INSTANCE_DIR)/tf-helm-pim-values.yaml" 'mailer.password'); \
	cd $(DEPLOYMENT_DIR)/timmy && go run timmy-create.go $(PFID) $(INSTANCE_NAME) $${MYSQL_PASSWORD} $${MAILGUN_PASSWORD} $(DEPLOYMENT_HASH)

## Cleanup tenants
.PHONY: delete_tenant
delete_tenant: generate_deployment_files
	helm3 delete $(PFID) --namespace $(PFID)
	cd $(INSTANCE_DIR) && terraform init $(TF_INPUT_FALSE) -upgrade
	cd $(INSTANCE_DIR) && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)
	kubectl delete ns $(PFID)
	cd $(DEPLOYMENT_DIR)/timmy && go run timmy-remove.go $(PFID)

###################
### infra				###
###################
## Deploy argocd
.PHONY: deploy_argocd
deploy_argocd:
	helm3 repo add argo https://argoproj.github.io/argo-helm
	helm3 repo update 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 upgrade --debug --atomic --cleanup-on-fail --history-max 5 --install --wait --timeout 20m --create-namespace --namespace argocd argocd argo/argo-cd -f k8s/argocd/values.yaml
	kubectl apply -f k8s/argocd/templates/argocd-cm.yaml

## Cleanup argocd
.PHONY: delete_argocd
delete_argocd:
	kubectl delete -f k8s/argocd/template/argocd-cm.yaml
	helm3 delete argocd --namespace argocd
	kubectl delete ns argocd

## Deploy crossplane
.PHONY: deploy_crossplane
deploy_crossplane:
	helm repo add crossplane-stable https://charts.crossplane.io/stable
	helm repo update 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 upgrade --debug --atomic --cleanup-on-fail --history-max 5 --install --wait --timeout 20m --create-namespace --namespace crossplane-system crossplane crossplane-stable/crossplane
	kubectl apply -f k8s/crossplane/templates/gcp-authentication.yaml
	kubectl apply -f k8s/crossplane/templates/provider-jet-gcp.yaml
	# Can take some time ...
	kubectl apply -f k8s/crossplane/templates/provider-config-jet-gcp.yaml

.PHONY: datadog
datadog:
	helm3 repo add datadog https://helm.datadoghq.com
	helm3 repo update 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 upgrade --debug --atomic --cleanup-on-fail --history-max 5 --install --wait --timeout 20m --create-namespace --namespace default datadog-agent datadog/datadog -f k8s/datadog/values.yaml

.PHONY: deploy_secretmngr_csi
deploy_secretmngr_csi:
	helm3 repo add secrets-store-csi-driver https://kubernetes-sigs.github.io/secrets-store-csi-driver/charts
	helm3 repo update 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 upgrade csi-secrets-store secrets-store-csi-driver/secrets-store-csi-driver --namespace kube-system --set-string enableSecretRotation=true --set-string secretObjects=true
  kubectl apply -f https://raw.githubusercontent.com/GoogleCloudPlatform/secrets-store-csi-driver-provider-gcp/release-1.1/deploy/provider-gcp-plugin.yaml

###################
### TEST				###
###################
.PHONY: test-prod
test-prod:
	export KUBECONFIG=$(INSTANCE_DIR)/.kubeconfig
	FN_1=5;	FN_2=0;\
	while ! host $(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS); do \
			TIME_TO_SLEEP=`expr $${FN_1} + $${FN_2}`; FN_1=$${FN_2}; FN_2=$${TIME_TO_SLEEP}; \
			if [ $${TIME_TO_SLEEP} -gt $(MAX_DNS_TEST_TIMEOUT) ]; then echo 'DNS resolution issue on "$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS)"';exit 1; fi; \
		echo 'Waiting for DNS "$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS)" to be ready'; sleep $${TIME_TO_SLEEP} ; \
	done
	helm3 test ${PFID} -n ${PFID} --debug

###################
### Old UCS			###
### To remove? 	###
###################
.PHONY: deploy_traefik
deploy_traefik:
	rm -Rf "./traefik-chart"
	helm3 repo add traefik https://helm.traefik.io/traefik
	helm3 repo update 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 pull traefik/traefik --version v10.13.0 --untar --untardir "./traefik-chart"
	kubectl apply -f "./traefik-chart/traefik/crds"
	rm -Rf "./traefik-chart"
	helm3 upgrade traefik-v2 traefik/traefik \
		--debug \
		--atomic \
		--cleanup-on-fail \
		--create-namespace \
		--wait \
		--timeout 10m \
		--install \
		--version=10.13.0 \
		--namespace=traefik-v2 \
		--values "./traefik/static-configuration.yaml"

.PHONY: delete_traefik
delete_traefik:
	rm -Rf "./traefik-chart"
	helm3 repo add traefik https://helm.traefik.io/traefik
	helm3 repo update 2>&1 | grep -v "skipping loading invalid entry"; test $${PIPESTATUS[0]} -eq 0
	helm3 pull traefik/traefik --version v10.13.0 --untar --untardir "./traefik-chart"
	helm3 uninstall traefik-v2 traefik/traefik \
		--timeout 10m \
		--namespace=traefik-v2
	kubectl delete -f "./traefik-chart/traefik/crds" || true
	rm -Rf "./traefik-chart"
