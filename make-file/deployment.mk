# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)
PIM_SRC_DIR ?= ..
CI ?= false

#by default, the tag to deploy is CIRCLECI_SHA1
DEPLOY_SHA1 ?= $(IMAGE_TAG)

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

#Vars for exec_in
executor ?= kubectl
migrate ?= no

.PHONY: get-kubeconfig
get-kubeconfig:
	gcloud container clusters get-credentials $(GOOGLE_CLUSTER_NAME) --zone=$(GOOGLE_CLUSTER_ZONE) --project=$(GOOGLE_PROJECT_ID)

.PHONY: helm-prepare
helm-prepare:
	helm init --client-only
	helm plugin install https://github.com/hayorov/helm-gcs --version 0.2.2 || true
	helm repo add $(HELM_REPO_PROD) gs://$(HELM_REPO_PROD)/
	helm repo update

.PHONY: deploy
deploy: terraform-deploy

.PHONY: terraform-deploy
terraform-deploy: terraform-init terraform-apply

.PHONY: terraform-init
terraform-init:
	cd ~/4.x/ && terraform init $(TF_INPUT_FALSE) -upgrade
	ln -sf ~/project/deployments/pim/ ~/4.x/.terraform/modules/pim

.PHONY: terraform-apply
terraform-apply:
	cd ~/4.x/ && terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: delete
delete: terraform-delete clean-all

.PHONY: clean-all
clean-all: get-kubeconfig
	kubectl delete all,pvc --all -n $(PFID) --force --grace-period=0
	kubectl delete ns $(PFID)

.PHONY: terraform-plan-destroy
terraform-plan-destroy: terraform-init
	cd ~/4.x/ && terraform plan -destroy $(TF_INPUT_FALSE)

.PHONY: terraform-delete
terraform-delete: terraform-init
	cd ~/4.x/ && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: create-tf-files
create-tf-files:
	@echo "Deploy with $(DEPLOY_SHA1)"
	mkdir -p ~/4.x/
	cp ~/project/deployments/config/ci-values.yaml ~/4.x/values.yaml
	yq w -i ~/project/deployments/pim/Chart.yaml version ${DEPLOY_SHA1}
	@echo $(INSTANCE_NAME_PREFIX)
ifeq ($(INSTANCE_NAME_PREFIX),pimup)
	yq w -i ~/4.x/values.yaml pim.hook.installPim.enabled true
	yq w -i ~/4.x/values.yaml pim.hook.upgradePim.enabled true
	yq w -i ~/4.x/values.yaml pim.hook.upgradeES.enabled true
endif
	@echo "terraform {" >> ~/4.x/main.tf
	@echo "backend \"gcs\" {" >> ~/4.x/main.tf
	@echo "bucket  = \"akecld-terraform\"" >> ~/4.x/main.tf
	@echo "prefix  = \"saas/$(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)/$(PFID)/\"" >> ~/4.x/main.tf
	@echo "project = \"akeneo-cloud\"" >> ~/4.x/main.tf
	@echo "}" >> ~/4.x/main.tf
	@echo "}" >> ~/4.x/main.tf
	@echo "module \"pim\" {" >> ~/4.x/main.tf
	@echo "source = \"/root/project/deployments/terraform\"" >> ~/4.x/main.tf
	@echo "google_project_id                 = \"$(GOOGLE_PROJECT_ID)\"" >> ~/4.x/main.tf
	@echo "google_project_zone                 = \"$(GOOGLE_CLUSTER_ZONE)\"" >> ~/4.x/main.tf
	@echo "instance_name                       = \"$(INSTANCE_NAME)\"" >> ~/4.x/main.tf
	@echo "dns_external                        = \"$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS).\"" >> ~/4.x/main.tf
	@echo "dns_internal                        = \"$(CLUSTER_DNS_NAME)\"" >> ~/4.x/main.tf
	@echo "dns_zone                            = \"$(GOOGLE_MANAGED_ZONE_NAME)\"" >> ~/4.x/main.tf
	@echo "pager_duty_service_key              = \"d55f85282a8e4e16b2c822249ad440bd\"" >> ~/4.x/main.tf
	@echo "google_storage_location             = \"eu\"" >> ~/4.x/main.tf
	@echo "papo_project_code                   = \"NOT_ON_PAPO\"" >> ~/4.x/main.tf
	@echo "force_destroy_storage               = true" >> ~/4.x/main.tf
	@echo "pim_version                         = \"$(IMAGE_TAG)\"" >> ~/4.x/main.tf
	@echo "}" >> ~/4.x/main.tf

.PHONY: test-prod
test-prod:
	helm test ${PFID}
