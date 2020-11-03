# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

# Force bash compatibility (Instead of user default shell)
SHELL := /bin/bash

INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)
CI ?= false
ACTIVATE_MONITORING ?= true

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
DEPLOYMENTS_INSTANCES_DIR ?= $(PWD)/deployments/instances
INSTANCE_DIR ?= $(DEPLOYMENTS_INSTANCES_DIR)/$(PFID)
MAX_DNS_TEST_TIMEOUT ?= 300
ONBOARDER_PIM_GEN_FILE ?=
WITH_SUPPLIERS ?= false

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

.PHONY: deploy-serenity
deploy-serenity: create-ci-release-files deploy

.PHONY: delete-serenity
delete-serenity: create-ci-release-files delete

.PHONY: deploy
deploy: terraform-deploy
	@echo "#######################################################################################"
	@echo ""
	@echo "This environment is available at https://$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS) :)"
	@echo ""
	@echo "K9s direct access command line: k9s -n $(PFID) -c pods"
	@echo ""
	@echo "#######################################################################################"

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
	cd $(INSTANCE_DIR) && TF_LOG=TRACE TF_LOG_PATH=terraform.log terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: prepare-infrastructure-artifacts
prepare-infrastructure-artifacts: render-helm-templates
	mkdir -p ~/artifacts/infra
	cp -raT $(DEPLOYMENTS_INSTANCES_DIR) ~/artifacts/infra/ || true
	rm -Rf ~/artifacts/infra/**/.terraform || true
	rm -Rf ~/artifacts/infra/**/.kubeconfig || true

.PHONY: render-helm-templates
render-helm-templates:
	cd $(INSTANCE_DIR) ;\
	mkdir -p helm-render ;\
	helm template .terraform/modules/pim/pim -f tf-helm-pim-values.yaml -f values.yaml -n $(PFID) --output-dir helm-render || true
ifeq ($(INSTANCE_NAME_PREFIX),pimup32)
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 ;\
	mkdir -p helm-render ;\
	helm template .terraform/modules/pim/pim -f tf-helm-pim-values.yaml -f values.yaml -n $(PFID) --output-dir helm-render || true
endif

.PHONY: delete
delete:
	if [ -f "$(INSTANCE_DIR)/main.tf.json" ]; then \
		cd $(INSTANCE_DIR) ;\
		echo "Destroying $(INSTANCE_DIR) ..." ;\
		INSTANCE_NAME=$(INSTANCE_NAME) TF_INPUT_FALSE=$(TF_INPUT_FALSE) TF_AUTO_APPROVE=$(TF_AUTO_APPROVE) bash $(PWD)/deployments/bin/delete_srnt_instance.sh ;\
	elif [ -f "$(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf" ]; then \
		cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 ;\
		echo "Destroying $(DEPLOYMENTS_INSTANCES_DIR)/3.2 ..." ;\
		INSTANCE_NAME=$(INSTANCE_NAME) TF_INPUT_FALSE=$(TF_INPUT_FALSE) TF_AUTO_APPROVE=$(TF_AUTO_APPROVE) bash $(PWD)/deployments/bin/delete_srnt_instance.sh ;\
	fi

.PHONY: create-ci-release-files
create-ci-release-files: create-ci-values create-pim-main-tf

.PHONY: create-ci-values
create-ci-values: $(INSTANCE_DIR)
	@echo "=========================================================="
	@echo "Deploy namespace : $(PFID)"
	@echo " - with image tag : $(IMAGE_TAG)"
	@echo " - on cluster : $(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)"
	@echo " - URL : $(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS)"
	@echo "=========================================================="
	cp $(PIM_SRC_DIR)/deployments/config/ci-values.yaml $(INSTANCE_DIR)/values.yaml
ifeq ($(INSTANCE_NAME_PREFIX),pimup)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.installPim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradePim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradeES.enabled true
endif
ifeq ($(INSTANCE_NAME_PREFIX),pimup32)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.intermediateUpgrades[+] "v20200211172331"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.intermediateUpgrades[+] "v20200401020139"
endif
ifeq ($(INSTANCE_NAME),pimci-helpdesk)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.installPim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradePim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradeES.enabled false
endif
ifeq ($(INSTANCE_NAME_PREFIX),pimci-pr)
	sed 's/^\(FLAG_.*_ENABLED\).*/  \1: "1"/g' .env | (grep "FLAG_.*_ENABLED"|grep -v "ONBOARDER"|grep -v "SHARED_CATALOG" || true) >> $(PIM_SRC_DIR)/deployments/terraform/pim/templates/env-configmap.yaml
endif
ifeq ($(INSTANCE_NAME_PREFIX),beta)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.installPim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradePim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradeES.enabled true
endif
ifeq ($(INSTANCE_NAME_PREFIX),pimci-duplic)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.daemons.default.resources.limits.memory "2048Mi"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.daemons.default.resources.requests.memory "2048Mi"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.daemons.default.resources.requests.cpu "200m"
	yq w -i $(INSTANCE_DIR)/values.yaml mysql.common.persistentDisks[0] $(PFID)
	yq w -i $(INSTANCE_DIR)/values.yaml mysql.mysql.userPassword test
	yq w -i $(INSTANCE_DIR)/values.yaml mysql.mysql.rootPassword test
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultAdminUser.email "adminakeneo"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultAdminUser.login "adminakeneo"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultAdminUser.password "adminakeneo"
endif
ifneq (${ONBOARDER_PIM_GEN_FILES_DIR},)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog "vendor/akeneo/pim-onboarder/src/Bundle/Resources/fixtures/onboarder"
ifneq ($(wildcard ${ONBOARDER_PIM_GEN_FILES_DIR}/pim-values.yaml),)
	# "gen_files/pim-values.yaml" is generated by Terraform during the Onboarder install. So it will not exist if we
	# perform a PIM desinstallation from a clean workspace (no Onboarder installation artifacts present). This is not
	# an issue to do so, as for removal the PIM Terraform scripts only need "values.yaml" to exist, whatever is in it.
	yq m -i $(INSTANCE_DIR)/values.yaml ${ONBOARDER_PIM_GEN_FILES_DIR}/pim-values.yaml
endif
ifeq ($(WITH_SUPPLIERS),true)
	yq w -i $(INSTANCE_DIR)/values.yaml onboarder.hook.importOnboarderSuppliers.enabled true
endif
endif

.PHONY: create-pim-main-tf
create-pim-main-tf: $(INSTANCE_DIR)
ifeq ($(ACTIVATE_MONITORING),true)
	jq -s '.[0] * .[1]' $(PWD)/deployments/config/serenity_instance.tpl.tf.json  $(PWD)/deployments/config/serenity_instance_monitoring.tpl.tf.json  > $(INSTANCE_DIR)/serenity_instance.tpl.tf.json.tmp
else
	cat $(PWD)/deployments/config/serenity_instance.tpl.tf.json > $(INSTANCE_DIR)/serenity_instance.tpl.tf.json.tmp
	@echo "-- WARNING: MONITORING is not activated on this PR. If your PR impact monitoring, please activate it."
	@echo "To activate it show 'deactivate_monitoring' and 'ACTIVATE_MONITORING' on '.CircleCi/config.yml'"
endif
	CLUSTER_DNS_NAME=$(CLUSTER_DNS_NAME) \
	GOOGLE_CLUSTER_ZONE=$(GOOGLE_CLUSTER_ZONE) \
	GOOGLE_MANAGED_ZONE_DNS=$(GOOGLE_MANAGED_ZONE_DNS) \
	GOOGLE_MANAGED_ZONE_NAME=$(GOOGLE_MANAGED_ZONE_NAME) \
	GOOGLE_PROJECT_ID=$(GOOGLE_PROJECT_ID) \
	IMAGE_TAG=$(IMAGE_TAG) \
	INSTANCE_NAME=$(INSTANCE_NAME) \
	PFID=$(PFID) \
	PIM_SRC_DIR=$(PIM_SRC_DIR) \
	envsubst < $(INSTANCE_DIR)/serenity_instance.tpl.tf.json.tmp > $(INSTANCE_DIR)/main.tf.json ;\
	rm -rf $(INSTANCE_DIR)/serenity_instance.tpl.tf.json.tmp && cat $(INSTANCE_DIR)/main.tf.json

.PHONY: test-prod
test-prod:
	export KUBECONFIG=$(INSTANCE_DIR)/.kubeconfig
	FN_1=5;	FN_2=0;\
	while ! host $(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS); do \
			TIME_TO_SLEEP=`expr $${FN_1} + $${FN_2}`; FN_1=$${FN_2}; FN_2=$${TIME_TO_SLEEP}; \
			if [ $${TIME_TO_SLEEP} -gt $(MAX_DNS_TEST_TIMEOUT) ]; then echo 'DNS resolution issue on "$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS)"';exit 1; fi; \
		echo 'Waiting for DNS "$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS)" to be ready'; sleep $${TIME_TO_SLEEP} ; \
	done
	helm test --debug --logs ${PFID}

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
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make deploy-serenity && \
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make slack_helpdesk

.PHONY: slack_helpdesk
slack_helpdesk:
	curl -X POST -H 'Content-type: application/json' --data '{"text":"Serenity env has been deployed with the last tag $(IMAGE_TAG) : https://pimci-helpdesk.preprod.cloud.akeneo.com"}' $${SLACK_URL_HELPDESK};

.PHONY: delete_pr_environments_hourly
delete_pr_environments_hourly:
	bash $(PWD)/deployments/bin/remove_pr_instances.sh

.PHONY: clone_serenity
clone_serenity:
	INSTANCE_NAME=${INSTANCE_NAME}  IMAGE_TAG=$(SOURCE_PED_TAG) INSTANCE_NAME_PREFIX=pimci-duplic make create-ci-release-files && \
	ENV_NAME=dev SOURCE_PFID=$(SOURCE_PFID) SOURCE_PED_TAG=$(SOURCE_PED_TAG) INSTANCE_NAME=$(INSTANCE_NAME) bash $(PWD)/deployments/bin/clone_serenity.sh

.PHONY: test_upgrade_from_serenity_customer_db
test_upgrade_from_serenity_customer_db:
	INSTANCE_NAME=${INSTANCE_NAME}  IMAGE_TAG=$(SOURCE_PED_TAG) INSTANCE_NAME_PREFIX=pimci-duplic make create-ci-release-files && \
	ENV_NAME=dev SOURCE_PFID=$(SOURCE_PFID) SOURCE_PED_TAG=$(SOURCE_PED_TAG) INSTANCE_NAME=$(INSTANCE_NAME) bash $(PWD)/deployments/bin/clone_serenity.sh && \
	INSTANCE_NAME_PREFIX=pimci-duplic INSTANCE_NAME=${INSTANCE_NAME} IMAGE_TAG=$${CIRCLE_SHA1} make deploy-serenity
