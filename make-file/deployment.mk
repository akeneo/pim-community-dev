# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

INSTANCE_NAME ?= pimci-${IMAGE_TAG}
PFID ?= srnt-${INSTANCE_NAME}
PIM_VERSION_BUILD ?= master
PIM_SRC_DIR ?= ..
CI ?= false

ifndef INSTANCE_NAME
$(error INSTANCE_NAME is not set)
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
	INSTANCE_DIR ?= ../$(PFID)
	TF_INPUT_FALSE ?= -input=false
	TF_AUTO_APPROVE ?= -auto-approve
	PIM_SRC_DIR ?= ..
else
	# When not using the CI, consider PFID as the instance dir.
	INSTANCE_DIR ?= ./$(PFID)
	TF_INPUT_FALSE =
	TF_AUTOAPPROVE =

ifeq ($(PIM_SRC_DIR),)
$(error PIM_SRC_DIR is empty, please define with path to pim-ai src repository)
endif
endif

TERRAFORM = cd ${INSTANCE_DIR}/terraform && terraform
HELM_REPO_PROD := akeneo-charts

#Vars for exec_in
executor ?= kubectl
migrate ?= no

.PHONY: get-kubeconfig
get-kubeconfig:
	gcloud container clusters get-credentials $(GOOGLE_CLUSTER_NAME) --zone=$(GOOGLE_CLUSTER_ZONE) --project=$(GOOGLE_PROJECT_ID)

.PHONY: helm-prepare
helm-prepare:
	helm init --client-only
	helm plugin install https://github.com/nouney/helm-gcs --version 0.2.0 || true
	helm repo add $(HELM_REPO_PROD) gs://$(HELM_REPO_PROD)/
	helm repo update

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

.PHONY: deploy
deploy: terraform-deploy

.PHONY: delete
delete: terraform-delete clean-all

.PHONY: clean-all
clean-all: get-kubeconfig
	kubectl delete all,pvc --all -n $(PFID) --force --grace-period=0
	kubectl delete ns $(PFID)

.PHONY: terraform-plan-destroy
terraform-plan-destroy: terraform-init
	$(TERRAFORM) plan -destroy $(TF_INPUT_FALSE)

.PHONY: terraform-delete
terraform-delete: terraform-init
	$(TERRAFORM) destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: create-tf-files
create-tf-files: $(INSTANCE_DIR) $(INSTANCE_DIR)/terraform/terraform.tfvars

.PHONY: create-helm-files
create-tf-files: $(INSTANCE_DIR)/terraform/values.yaml

$(INSTANCE_DIR):
	cp -R ./deployments/ $(INSTANCE_DIR)

$(INSTANCE_DIR)/terraform/values.yaml: $(INSTANCE_DIR)
	cp ./deployments/config/ci-values.yaml $(INSTANCE_DIR)/terraform/values.yaml
	yq w -i $(INSTANCE_DIR)/terraform/values.yaml image.pim.tag ${IMAGE_TAG}

$(INSTANCE_DIR)/terraform/terraform.tfvars: $(INSTANCE_DIR)
	@echo "instance_name                       = \"$(INSTANCE_NAME)\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "google_project_zone                 = \"$(GOOGLE_CLUSTER_REGION)\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "google_project_id                   = \"$(GOOGLE_PROJECT_ID)\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "dns_external                        = \"$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS).\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "dns_internal                        = \"$(CLUSTER_DNS_NAME)\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "dns_zone                            = \"$(GOOGLE_MANAGED_ZONE_NAME)\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "pager_duty_service_key              = \"d55f85282a8e4e16b2c822249ad440bd\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "google_storage_location             = \"eu\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "papo_project_code                   = \"NOT_ON_PAPO\"" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo "force_destroy_storage               = true" >> $(INSTANCE_DIR)/terraform/terraform.tfvars
	@echo $(INSTANCE_DIR)/terraform/terraform.tfvars file created

.PHONY: test-pim-connection
test-pim-connection:
	helm test ${PFID}

.PHONY: test-prod
test-prod:
	exit 1

.PHONY: release
release:
	exit 1
