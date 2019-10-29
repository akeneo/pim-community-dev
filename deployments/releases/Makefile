# Adapted CircleCi Jobs (CI=true) OR manual run from release dir
# Example from release dir: make -f ~/<pim-ai_repo_dir>/infra/Makefile terraform-plan

INSTANCE_NAME ?= tip-1315
PFID ?= srnt-$(INSTANCE_NAME)
PIM_VERSION_BUILD ?= master
HELM_CHART_VERSION ?= 4.0.0-master
PIM_SRC_DIR ?= ..
CI ?= false


HELM_CHART_VERSION ?= $(PIM_VERSION_BUILD)
ifndef PFID
$(error PFID is not set)
endif
ifndef HELM_CHART_VERSION
$(error HELM_CHART_VERSION is not set)
endif

# If TEST_AUTO="true" for CI automatic tests, use a dedicated "received_product" topic to avoid services pollution
TEST_AUTO ?= false
ENV_NAME ?= dev
GOOGLE_PROJECT_ID ?= akecld-saas-$(ENV_NAME)
GOOGLE_CLUSTER_REGION ?= europe-west3
GOOGLE_CLUSTER_ZONE ?= europe-west3-a
GOOGLE_CLUSTER_NAME ?= $(GOOGLE_CLUSTER_ZONE)
GOOGLE_MANAGED_ZONE_DNS ?= $(ENV_NAME).cloud.akeneo.com
GOOGLE_MANAGED_ZONE_NAME ?= $(ENV_NAME)-cloud-akeneo-com
CLUSTER_DNS_NAME ?= europe-west3-a-akecld-saas-$(ENV_NAME).$(ENV_NAME).cloud.akeneo.com.
GOOGLE_STORAGE_lOCATION ?= eu
REGISTRY ?= eu.gcr.io

ifeq ($(CI),true)
	# Run from pim-ai/infra directory
	INSTANCE_DIR ?= ./$(PFID)
	HELM_VALUES_OPT ?= 
	TF_INPUT_FALSE ?= -input=false
	TF_AUTO_APPROVE ?= -auto-approve
	PIM_SRC_DIR ?= ..
else
	# When not using the CI, consider PWD as the instance dir.
	#INSTANCE_DIR ?= $(PWD)
	INSTANCE_DIR ?= ./$(PFID)
	HELM_VALUES_OPT ?= 
	TF_INPUT_FALSE =
	TF_AUTOAPPROVE =
ifeq ($(PIM_SRC_DIR),)
$(error PIM_SRC_DIR is empty, please define with path to pim-ai src repository)
endif
endif

ifeq ($(TEST_AUTO),true)
	HELM_VALUES_OPT := $(HELM_VALUES_OPT) -f ./config/test-values.yaml
endif

TERRAFORM = cd $(INSTANCE_DIR) && terraform

HELM_REPO_DEV := akeneo-charts-dev
HELM_REPO_PROD := akeneo-charts
HELM_REPO ?= $(HELM_REPO_DEV)
HELM_CHART_NAME ?= pim
HELM_TIMEOUT ?= 1200

#Vars for exec_in
executor ?= kubectl
migrate ?= no
EXEC_IN = executor=$(executor) migrate=$(migrate) $(PIM_SRC_DIR)/bin/exec_in

.PHONY: get-kubeconfig
get-kubeconfig:
	gcloud container clusters get-credentials $(GOOGLE_CLUSTER_NAME) --zone=$(GOOGLE_CLUSTER_ZONE) --project=$(GOOGLE_PROJECT_ID)

.PHONY: terraform-deploy
terraform-deploy: terraform-init terraform-plan terraform-apply

.PHONY: terraform-init
terraform-init:
	$(TERRAFORM) init $(TF_INPUT_FALSE) -upgrade -backend-config="prefix=saas/$(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_NAME)/$(PFID)"

.PHONY: terraform-plan
terraform-plan: terraform-init
	$(TERRAFORM) plan $(TF_INPUT_FALSE) -out=tfplan

.PHONY: terraform-apply
terraform-apply: terraform-init
	$(TERRAFORM) apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE) tfplan

.PHONY: helm-prepare
helm-prepare:
	helm repo add $(HELM_REPO_DEV) gs://$(HELM_REPO_DEV)/
	helm repo add $(HELM_REPO_PROD) gs://$(HELM_REPO_PROD)/
	helm repo update
	helm fetch  $(HELM_REPO)/$(HELM_CHART_NAME) --version $(HELM_CHART_VERSION)

.PHONY: helm-upgrade
helm-upgrade: get-kubeconfig
	helm upgrade --debug --install --wait --timeout=900 --force $(PFID) --namespace $(PFID) $(HELM_REPO)/$(HELM_CHART_NAME) --version $(HELM_CHART_VERSION) -f $(INSTANCE_DIR)/tf-helm-pim-values.yaml -f $(INSTANCE_DIR)/pim.yaml $(HELM_VALUES_OPT)

.PHONY: deploy
deploy: terraform-deploy helm-prepare helm-upgrade 

.PHONY: delete
delete: helm-delete-all terraform-delete

.PHONY: helm-delete-release # Keep the PVC!
helm-delete-release: get-kubeconfig
	helm delete --purge $(PFID)
	kubectl delete ns $(PFID)

.PHONY: helm-delete-all
helm-delete-all: get-kubeconfig
	helm delete --purge $(PFID)
	# workaround to clean https://github.com/kubernetes/helm/issues/4019
	kubectl delete all,pvc --all -n $(PFID) --force --grace-period=0
	kubectl delete ns $(PFID)


.PHONY: terraform-plan-destroy
terraform-plan-destroy: terraform-init
	$(TERRAFORM) plan -destroy $(TF_INPUT_FALSE)


.PHONY: terraform-delete
terraform-delete: terraform-init
	$(TERRAFORM) destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

:PHONY: create-tf-files
create-tf-files: $(INSTANCE_DIR) $(INSTANCE_DIR)/terraform.tfvars

$(INSTANCE_DIR):
	cp -R ./terraform $(INSTANCE_DIR)

$(INSTANCE_DIR)/terraform.tfvars: $(INSTANCE_DIR)
	@echo "pfid                                = \"$(PFID)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "external_fqdn                       = \"$(PFID).$(GOOGLE_MANAGED_ZONE_DNS)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "dns_zone_name                       = \"$(GOOGLE_MANAGED_ZONE_NAME)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "MAILGUN_SMTP_USER                   = \"$(PFID)-$(GOOGLE_PROJECT_ID)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@if [ "$(TEST_AUTO)" = "true" ]; then \
		echo "received_product_topic_name         = \"pim-ai-received_product-dev-$(PFID)\"" >> $(INSTANCE_DIR)/terraform.tfvars ; \
	else  \
		echo "received_product_topic_name         = \"pim-ai-received_product-dev-shared\"" >> $(INSTANCE_DIR)/terraform.tfvars  ; \
	fi
	@echo "" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "google_project_id                 = \"$(GOOGLE_PROJECT_ID)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "google_cluster_zone                 = \"$(GOOGLE_CLUSTER_ZONE)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "google_project_region               = \"$(GOOGLE_CLUSTER_REGION)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "dns_internal                        = \"$(GOOGLE_CLUSTER_ZONE)-$(GOOGLE_PROJECT_ID).$(GOOGLE_MANAGED_ZONE_DNS).\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "received_product_topic_project_id   = \"$(GOOGLE_PROJECT_ID)\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo "pager_duty_service_key              = \"non-existent-key\"" >> $(INSTANCE_DIR)/terraform.tfvars
	@echo $(INSTANCE_DIR)/terraform.tfvars file created


