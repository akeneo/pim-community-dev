# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)
CI ?= false

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
HELM_REPO_PROD := akeneo-charts
DEPLOYMENTS_INSTANCES_DIR = $(PWD)/deployments/instances
INSTANCE_DIR ?= $(DEPLOYMENTS_INSTANCES_DIR)/$(PFID)

ifeq ($(CI),true)
	TF_INPUT_FALSE ?= -input=false
	TF_AUTO_APPROVE ?= -auto-approve
	# CircleCi Checkout Directory , need to avoid ~ for terraform module path
	PIM_SRC_DIR ?= /root/project
else
	TF_INPUT_FALSE ?=
	TF_AUTOAPPROVE ?=
	PIM_SRC_DIR ?= $(PWD)
	# considering we use Makefile from the root of the project repo
endif

#Vars for exec_in
executor ?= kubectl
migrate ?= no

.PHONY: helm-prepare
helm-prepare:
	helm init --client-only
	helm repo add $(HELM_REPO_PROD) gs://$(HELM_REPO_PROD)/
	helm repo update

.PHONY: deploy
deploy: terraform-deploy

.PHONY: terraform-deploy
terraform-deploy: terraform-init terraform-apply

$(INSTANCE_DIR):
	mkdir -p $(INSTANCE_DIR)

.PHONY: terraform-init
terraform-init: $(INSTANCE_DIR)
	cd $(INSTANCE_DIR) && terraform init $(TF_INPUT_FALSE) -upgrade

.PHONY: terraform-plan
terraform-plan: terraform-init
	cd $(INSTANCE_DIR) && terraform plan

.PHONY: terraform-apply
terraform-apply:
	cd $(INSTANCE_DIR) && terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: delete
delete: terraform-delete purge-release-all

.PHONY: purge-release-all
purge-release-all:
	helm delete $(PFID) --purge || echo "WARNING: FAILED helm delete --purge $(PFID)"
	@kubectl delete all,pvc --all -n $(PFID) --force --grace-period=0 && echo "kubectl delete all,pvc forced OK" || echo "WARNING: FAILED kubectl delete all,pvc --all -n $(PFID) --force --grace-period=0"
	@kubectl delete ns $(PFID) && echo "kubectl delete ns OK"  || echo "WARNING: FAILED kubectl delete ns $(PFID)"

.PHONY: terraform-plan-destroy
terraform-plan-destroy: terraform-init
	cd $(INSTANCE_DIR) && terraform plan -destroy $(TF_INPUT_FALSE)

.PHONY: terraform-delete
terraform-delete: terraform-init
	cd $(INSTANCE_DIR) && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: create-ci-release-files
create-ci-release-files: create-ci-values create-pim-main-tf

.PHONY: create-ci-values
create-ci-values: $(INSTANCE_DIR)
	@echo "Deploy with $(IMAGE_TAG) - $(INSTANCE_DIR)/values.yaml"
	@echo "Create Helm values file : $(INSTANCE_DIR)/values.yaml"
	cp $(PIM_SRC_DIR)/deployments/config/ci-values.yaml $(INSTANCE_DIR)/values.yaml
ifeq ($(INSTANCE_NAME_PREFIX),pimup)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.installPim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradePim.enabled true	
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradeES.enabled true
endif

.PHONY: create-pim-main-tf
create-pim-main-tf: $(INSTANCE_DIR)
	@echo $(INSTANCE_NAME_PREFIX)
	@echo "terraform {" > $(INSTANCE_DIR)/main.tf
	@echo "backend \"gcs\" {" >> $(INSTANCE_DIR)/main.tf
	@echo "bucket  = \"akecld-terraform\"" >> $(INSTANCE_DIR)/main.tf
	@echo "prefix  = \"saas/$(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)/$(PFID)/\"" >> $(INSTANCE_DIR)/main.tf
	@echo "project = \"akeneo-cloud\"" >> $(INSTANCE_DIR)/main.tf
	@echo "}" >> $(INSTANCE_DIR)/main.tf
	@echo "}" >> $(INSTANCE_DIR)/main.tf
	@echo "module \"pim\" {" >> $(INSTANCE_DIR)/main.tf
	@echo "source = \"$(PIM_SRC_DIR)/deployments/terraform\"" >> $(INSTANCE_DIR)/main.tf
	@echo "google_project_id                 = \"$(GOOGLE_PROJECT_ID)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "google_project_zone                 = \"$(GOOGLE_CLUSTER_ZONE)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "instance_name                       = \"$(INSTANCE_NAME)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "dns_external                        = \"$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS).\"" >> $(INSTANCE_DIR)/main.tf
	@echo "dns_internal                        = \"$(CLUSTER_DNS_NAME)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "dns_zone                            = \"$(GOOGLE_MANAGED_ZONE_NAME)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "pager_duty_service_key              = \"d55f85282a8e4e16b2c822249ad440bd\"" >> $(INSTANCE_DIR)/main.tf
	@echo "google_storage_location             = \"eu\"" >> $(INSTANCE_DIR)/main.tf
	@echo "papo_project_code                   = \"NOT_ON_PAPO_$(PFID)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "force_destroy_storage               = true" >> $(INSTANCE_DIR)/main.tf
	@echo "pim_version                         = \"$(IMAGE_TAG)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "}" >> $(INSTANCE_DIR)/main.tf

.PHONY: test-prod
test-prod:
	export KUBECONFIG=$(INSTANCE_DIR)/.kubeconfig
	helm test ${PFID}

.PHONY: release
release:
ifeq ($(CI),true)
	git config user.name "Michel Tag"
	git remote set-url origin https://micheltag:${MICHEL_TAG_TOKEN}@github.com/akeneo/pim-enterprise-dev.git
endif
	bash $(PWD)/deployments/bin/release.sh ${OLD_IMAGE_TAG}

.PHONY: deploy_latest_release_for_helpdesk
deploy_latest_release_for_helpdesk:
	RELEASE_TO_DEPLOY=$$(cd ${PIM_SRC_DIR}; git fetch origin &> /dev/null && git tag --list | grep -E "^v?[0-9]+$$" | sort -r | head -n 1); \
	echo $${RELEASE_TO_DEPLOY};  \
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make create-ci-release-files; \
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make deploy && \
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make slack_helpdesk

.PHONY: slack_helpdesk
slack_helpdesk:
	curl -X POST -H 'Content-type: application/json' --data '{"text":"Serenity env has been deployed with the last tag $(IMAGE_TAG) : https://pimci-helpdesk.preprod.cloud.akeneo.com"}' $${SLACK_URL_HELPDESK};
