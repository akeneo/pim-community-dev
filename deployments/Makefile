# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

# Force bash compatibility (Instead of user default shell)
SHELL := /bin/bash

IMAGE_TAG ?= master
CI ?= 0
PIM_CONTEXT ?=0
USE_EDITION_FLAG ?= false

INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?=  $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG_SHORTED)
TYPE ?= srnt
DEPLOYMENT_DIR ?= $(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
PIM_SRC_PATH ?= $(realpath $(DEPLOYMENT_DIR)/..)
TYPE_LONG ?= serenity
ENV_NAME ?= dev
CIRCLE_PROJECT_REPONAME ?= local

BUCKET ?= serenity-edition-dev

ifeq ($(TYPE),grth)
	TYPE_LONG := growth_edition
	PIM_SRC_PATH := $(realpath $(DEPLOYMENT_DIR)/../$(TYPE))
	BUCKET := growth-edition-dev
endif

ifeq ($(TYPE),tria)
	TYPE_LONG := pim_trial
	PIM_SRC_PATH := $(realpath $(DEPLOYMENT_DIR)/../$(TYPE))
	BUCKET := trial-edition-dev
endif

# Active EditionFlag mode (All editions runs a srnt images and a ENV vars is set on pods for Symfony to know wich Feature Flag to enable)
# USE_EDITION_FLAG is compute by Terraform to set pfid=srnt-${instance-name} for all edition
# USE_EDITION_FLAG is compute by Terraform to set editionFlag.enabled=true in tf-helm-pim-values.tpl
# editionFlag.enabled=true in values.yml is compute by HELM to add PIM_EDITION:<editionflag> in env-configmap.yaml

ifeq ($(USE_EDITION_FLAG),true)
	TYPE := srnt
	PIM_SRC_PATH := $(realpath $(DEPLOYMENT_DIR)/..)
	BUCKET := serenity-edition-dev

#We don't hardcode TYPE_LONG to keep proper edition specific PRODUCT_REFERENCE_TYPE & PRODUCT_REFERENCE_CODE
endif

PRODUCT_REFERENCE_TYPE ?= $(TYPE_LONG)_instance
PRODUCT_REFERENCE_CODE ?= $(TYPE_LONG)_$(ENV_NAME)

PFID ?= $(TYPE)-$(INSTANCE_NAME)
CI ?= false
ACTIVATE_MONITORING ?= true

TEST_AUTO ?= false
GOOGLE_PROJECT_ID ?= akecld-saas-$(ENV_NAME)
GOOGLE_CLUSTER_REGION ?= europe-west3
GOOGLE_CLUSTER_ZONE ?= europe-west3-a
GOOGLE_CLUSTER_NAME ?= $(GOOGLE_CLUSTER_ZONE)
GOOGLE_MANAGED_ZONE_DNS ?= $(ENV_NAME).cloud.akeneo.com
GOOGLE_MANAGED_ZONE_NAME ?= $(ENV_NAME)-cloud-akeneo-com
CLUSTER_DNS_NAME ?= $(GOOGLE_CLUSTER_ZONE)-akecld-saas-$(ENV_NAME).$(ENV_NAME).cloud.akeneo.com.
GOOGLE_STORAGE_lOCATION ?= eu
REGISTRY ?= eu.gcr.io
HELM_REPO_PROD := akeneo-charts
PIM_ROOT_DIR ?= $(realpath $(DEPLOYMENT_DIR)/..)
DEPLOYMENTS_INSTANCES_DIR ?= $(DEPLOYMENT_DIR)/instances
INSTANCE_DIR := $(DEPLOYMENTS_INSTANCES_DIR)/$(PFID)
CLOUD_CUSTOMERS_DEV_DIR ?= $(DEPLOYMENT_DIR)/cloud-customers-dev
INSTANCE_CLOUD_CUSTOMERS_DEV_DIR ?= $(CLOUD_CUSTOMERS_DEV_DIR)/saas/projects/akecld-saas-dev/$(GOOGLE_CLUSTER_ZONE)/releases/$(PFID)
OPERATIONS_TOOLS_DIR ?= $(DEPLOYMENT_DIR)/operation-tools
OPERATIONS_TOOLS_BRANCH ?= master
MYSQL_DISK_SIZE ?= 10
MYSQL_DISK_DESCRIPTION ?=
MYSQL_SOURCE_SNAPSHOT ?=
MAX_DNS_TEST_TIMEOUT ?= 300
ONBOARDER_PIM_GEN_FILE ?=
WITH_SUPPLIERS ?= false
USE_ONBOARDER_CATALOG ?= false
UPGRADE_STEP_2 ?= false
BUCKET_PIM_SRC_DIR ?= gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/$(BUCKET)/$(IMAGE_TAG)
MAIN_TF_TEMPLATE ?= $(PRODUCT_REFERENCE_TYPE)
# If not set retrieve latest release of cloud-saas for storage-backup terraform module ( Default=Latest)
STORAGE_BACKUP_VERSION ?= $$(curl -s -H "Authorization: token ${GITHUB_TOKEN}" https://api.github.com/repos/akeneo/cloud-saas/releases/latest | jq -r '.name')

ifeq ($(CI),true)
	TF_INPUT_FALSE ?= -input=false
	TF_AUTO_APPROVE ?= -auto-approve
endif

TF_INPUT_FALSE ?=
TF_AUTOAPPROVE ?=

#Vars for exec_in
executor ?= kubectl
migrate ?= no

.DEFAULT_GOAL := help

.PHONY: help
help: #Doc: display this help
	@echo "$$(grep -hE '^\S+:.*#Doc:' $(MAKEFILE_LIST) | sed -e 's/:.*#Doc:\s*/:/' -e 's/^\(.\+\):\(.*\)/\1:-\ \2/' | column -c2 -t -s :)"

.PHONY: deploy-instance
deploy-instance: create-ci-release-files deploy

.PHONY: deploy-serenity
deploy-serenity: create-ci-release-files deploy
	@echo "Deprecated"

.PHONY: delete-serenity
delete-serenity: create-ci-release-files delete
	@echo "Deprecated"

.PHONY: delete-instance
delete-instance: create-ci-release-files delete

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
ifeq ($(UPGRADE_STEP_2),true)
		@echo "We are in the second step of update"
		cd $(INSTANCE_DIR) && STEP='PRE_INIT' INSTANCE_NAME=$(INSTANCE_NAME) bash $(DEPLOYMENT_DIR)/automation/upgrade.sh
endif
	cd $(INSTANCE_DIR) && cat main.tf.json
	cd $(INSTANCE_DIR) && terraform init $(TF_INPUT_FALSE) -upgrade

.PHONY: terraform-plan
terraform-plan: terraform-init
	cd $(INSTANCE_DIR) && terraform plan

.PHONY: terraform-apply
terraform-apply:
ifeq ($(UPGRADE_STEP_2),true)
		@echo "We are in the second step of update"
		cd $(INSTANCE_DIR) && STEP='PRE_APPLY' INSTANCE_NAME=$(INSTANCE_NAME) bash $(DEPLOYMENT_DIR)/automation/upgrade.sh
endif
	cd $(INSTANCE_DIR) && terraform plan '-out=upgrades.tfplan' $(TF_INPUT_FALSE) -compact-warnings
	cd $(INSTANCE_DIR) && terraform show -json upgrades.tfplan > ~/upgrades.tfplan.json
	cd $(INSTANCE_DIR) && HELM_DEBUG=true terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE) upgrades.tfplan

.PHONY: prepare-infrastructure-artifacts
prepare-infrastructure-artifacts: render-helm-templates
	mkdir -p ~/artifacts/infra
	cp -raT $(DEPLOYMENTS_INSTANCES_DIR) ~/artifacts/infra/ || true
	cp -raT $(DEPLOYMENT_DIR)/terraform/pim/templates/ ~/artifacts/infra/ || true
	rm -Rf ~/artifacts/infra/**/.terraform || true
	rm -Rf ~/artifacts/infra/**/.kubeconfig || true

.PHONY: render-helm-templates
render-helm-templates:
	cd $(INSTANCE_DIR) ;\
	mkdir -p helm-render ;\
	helm3 template .terraform/modules/pim/pim -f tf-helm-pim-values.yaml -f values.yaml -n $(PFID) --output-dir helm-render || true

.PHONY: delete
delete:
	if [ -f "$(INSTANCE_DIR)/main.tf.json" ]; then \
		cd $(INSTANCE_DIR) ;\
		echo "Destroying $(INSTANCE_DIR) ..." ;\
		ENV_NAME=$(ENV_NAME) TYPE=$(TYPE) INSTANCE_NAME=$(INSTANCE_NAME) TF_INPUT_FALSE=$(TF_INPUT_FALSE) TF_AUTO_APPROVE=$(TF_AUTO_APPROVE) bash $(DEPLOYMENT_DIR)/bin/delete_instance.sh ;\
	fi

.PHONY: create-ci-release-files
create-ci-release-files: create-ci-values create-pim-main-tf

.PHONY: create-ci-values
create-ci-values: $(INSTANCE_DIR)
	@echo "=========================================================="
	@echo "Deploy namespace : $(PFID)"
	@echo " - with instance name prefix : $(INSTANCE_NAME_PREFIX)"
	@echo " - with image tag : $(IMAGE_TAG)"
	@echo " - on cluster : $(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)"
	@echo " - URL : $(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS)"
	@echo "=========================================================="
	if [ ! -f $(INSTANCE_DIR)/values.yaml ]; then cp $(DEPLOYMENT_DIR)/config/ci-values.yaml $(INSTANCE_DIR)/values.yaml; fi
ifeq ($(INSTANCE_NAME_PREFIX),pimup)
ifeq ($(TYPE),srnt)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
endif
ifeq ($(TYPE),grth)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
endif
endif
ifeq ($(INSTANCE_NAME_PREFIX),pimci-pr)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
endif
ifeq ($(INSTANCE_NAME_PREFIX),pimci-pr-ge)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog vendor/akeneo/pim-community-dev/src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
endif
# Tricks to use OR like statement in ifeq -> https://stackoverflow.com/questions/7656425/makefile-ifeq-logical-or
ifneq (,$(filter $(INSTANCE_NAME_PREFIX),pimci-helpdesk pimci-helpdesk-ge))
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.installPim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradePim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradeES.enabled false
endif
ifneq (,$(filter $(INSTANCE_NAME_PREFIX),beta pimup))
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.installPim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradePim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradeES.enabled true
endif
ifeq ($(INSTANCE_NAME_PREFIX),pimci-duplic)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.daemons.job-consumer-process.resources.limits.memory "2048Mi"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.daemons.job-consumer-process.resources.requests.memory "2048Mi"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.daemons.job-consumer-process.resources.requests.cpu "200m"
endif
ifeq (${USE_ONBOARDER_CATALOG},true)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog "vendor/akeneo/pim-onboarder/src/Bundle/Resources/fixtures/onboarder"
endif
ifeq ($(TYPE_LONG),pim_trial)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.addAdmin.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.akeneo_connect_saml_entity_id "https://connect-sandbox.ip.akeneo.com/auth/realms/trial"
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.akeneo_connect_saml_signin_url "https://connect-sandbox.ip.akeneo.com/auth/realms/trial/protocol/saml"
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.akeneo_connect_saml_logout_url "https://connect-sandbox.ip.akeneo.com/auth/realms/trial/protocol/saml"
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.akeneo_connect_api_client_base_uri "https://connect-sandbox.ip.akeneo.com/"
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.akeneo_portal_api_client_base_uri "https://portal-dev4-sandbox.ip.akeneo.com/"
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.google_analytics_account_id "UA-113156045-4"
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.hotjar_id "2672696"
	yq w -i $(INSTANCE_DIR)/values.yaml free_trial.heap_id "2875170433"
endif

ifeq ($(USE_EDITION_FLAG),true)
ifeq ($(INSTANCE_NAME_PREFIX),pimci)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/minimal
endif
ifneq (,$(filter $(INSTANCE_NAME_PREFIX),pimup pimci-pr pimci-pr-ge))
	yq w -i $(INSTANCE_DIR)/values.yaml pim.defaultCatalog src/Akeneo/Platform/Bundle/InstallerBundle/Resources/fixtures/icecat_demo_dev
endif
endif

	@echo "cat $(DEPLOYMENT_DIR)/terraform/pim/values.yaml"
	cat $(DEPLOYMENT_DIR)/terraform/pim/values.yaml
	@echo "cat $(INSTANCE_DIR)/values.yaml"
	cat $(INSTANCE_DIR)/values.yaml

.PHONY: prepare-chart-default-values
prepare-chart-default-values:
	yq d -i $(DEPLOYMENT_DIR)/terraform/pim/values.yaml 'pim.jobs'
	yq m -i -x $(DEPLOYMENT_DIR)/terraform/pim/values.yaml $(DEPLOYMENT_DIR)/terraform/pim/values-$(TYPE).yaml
	cat $(DEPLOYMENT_DIR)/terraform/pim/values.yaml

.PHONY: get_mysql_parameters_disk
get_mysql_parameters_disk:
	MYSQL_DISK_SIZE=`gcloud compute disks describe --zone=$(GOOGLE_COMPUTE_ZONE) --project=$(GOOGLE_PROJECT_ID) $(PFID)-mysql --format=json |jq -r '.sizeGb'`; \
	MYSQL_SOURCE_SNAPSHOT=`gcloud compute disks describe --zone=$(GOOGLE_COMPUTE_ZONE) --project=$(GOOGLE_PROJECT_ID) $(PFID)-mysql --format=json |jq -r '.sourceSnapshot'`; \
	yq w -j -P -i $(INSTANCE_DIR)/main.tf.json 'module.pim.mysql_source_snapshot' "$${MYSQL_SOURCE_SNAPSHOT}"; \
	yq w -j -P -i $(INSTANCE_DIR)/main.tf.json 'module.pim.mysql_disk_size' "$${MYSQL_DISK_SIZE}"; \
	yq w -j -P -i $(INSTANCE_DIR)/main.tf.json 'module.pim.mysql_disk_name' "$(PFID)-mysql";

.PHONY: activate-onboarder-feature
activate-onboarder-feature:
ifneq ($(wildcard ${ONBOARDER_PIM_GEN_FILES_DIR}/pim-values.yaml),)
	# "gen_files/pim-values.yaml" is generated by Terraform during the Onboarder install. So it will not exist if we
	# perform a PIM desinstallation from a clean workspace (no Onboarder installation artifacts present). This is not
	# an issue to do so, as for removal the PIM Terraform scripts only need "values.yaml" to exist, whatever is in it.
	@yq m -ix ${INSTANCE_DIR}/values.yaml ${ONBOARDER_PIM_GEN_FILES_DIR}/pim-values.yaml
endif
	@yq w -i ${INSTANCE_DIR}/values.yaml onboarder.hook.importAdditionalOnboarderFixtures.enabled true
ifeq ($(WITH_SUPPLIERS),true)
	@yq w -i ${INSTANCE_DIR}/values.yaml onboarder.hook.importOnboarderSuppliers.enabled true
endif
	@yq w -i ${INSTANCE_DIR}/values.yaml onboarder.hook.pushCatalogToOnboarder.enabled true
	@make terraform-deploy
	@yq d -i ${INSTANCE_DIR}/values.yaml onboarder.hook.pushCatalogToOnboarder

.PHONY: create-pim-main-tf
create-pim-main-tf: $(INSTANCE_DIR)
	cat $(DEPLOYMENT_DIR)/config/$(MAIN_TF_TEMPLATE).tpl.tf.json > $(INSTANCE_DIR)/$(MAIN_TF_TEMPLATE).tpl.tf.json.tmp
	CLUSTER_DNS_NAME=$(CLUSTER_DNS_NAME) \
	GOOGLE_CLUSTER_ZONE=$(GOOGLE_CLUSTER_ZONE) \
	GOOGLE_MANAGED_ZONE_DNS=$(GOOGLE_MANAGED_ZONE_DNS) \
	GOOGLE_MANAGED_ZONE_NAME=$(GOOGLE_MANAGED_ZONE_NAME) \
	GOOGLE_PROJECT_ID=$(GOOGLE_PROJECT_ID) \
	IMAGE_TAG=$(IMAGE_TAG) \
	INSTANCE_NAME=$(INSTANCE_NAME) \
	PFID=$(PFID) \
	PROJECT_REPONAME=$(CIRCLE_PROJECT_REPONAME) \
	BUCKET_PIM_SRC_DIR=$(BUCKET_PIM_SRC_DIR) \
	PIM_SRC_DIR=$(PIM_ROOT_DIR) \
	TYPE=$(TYPE) \
	PRODUCT_REFERENCE_TYPE=$(PRODUCT_REFERENCE_TYPE) \
	PRODUCT_REFERENCE_CODE=$(PRODUCT_REFERENCE_CODE) \
	MYSQL_DISK_SIZE=$(MYSQL_DISK_SIZE) \
	MYSQL_DISK_NAME=$(PFID)-mysql \
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
	STORAGE_BACKUP_VERSION=${STORAGE_BACKUP_VERSION} \
	envsubst < $(INSTANCE_DIR)/$(MAIN_TF_TEMPLATE).tpl.tf.json.tmp > $(INSTANCE_DIR)/main.tf.json ;\
	rm -rf $(INSTANCE_DIR)/$(MAIN_TF_TEMPLATE).tpl.tf.json.tmp
ifeq ($(USE_EDITION_FLAG),true)
	yq w -j -P -i  ${INSTANCE_DIR}/main.tf.json  'module.pim.use_edition_flag' true
endif

.PHONY: change-terraform-source-version
change-terraform-source-version: #Doc: change terraform pim and monitoring source to deploy infra with a custom git version
	yq w -j -P -i ${INSTANCE_DIR}/main.tf.json 'module.pim.source' "gcs::https://www.googleapis.com/storage/v1/akecld-terraform-modules/$(BUCKET)/$(IMAGE_TAG)//deployments/terraform"
	@echo "Terraform pim and monitoring source updated"

$(CLOUD_CUSTOMERS_DEV_DIR):
	mkdir -p $(CLOUD_CUSTOMERS_DEV_DIR)
	git clone --depth 1 -b master --single-branch git@github.com:akeneo/cloud-customers-dev.git $(CLOUD_CUSTOMERS_DEV_DIR)

.PHONY: pull-cloud-customer-dev
pull-cloud-customer-dev: $(CLOUD_CUSTOMERS_DEV_DIR)
	cd $(CLOUD_CUSTOMERS_DEV_DIR) && git status -uall -s  && git pull --rebase

.PHONY: commit-instance
commit-instance: pull-cloud-customer-dev
	# INSTANCE_CLOUD_CUSTOMERS_DEV_DIR may not exist yet, need to use readlink
	cp -r $(INSTANCE_DIR) $(shell readlink -m $(INSTANCE_CLOUD_CUSTOMERS_DEV_DIR)/..)
	git config --global user.email "pim_ci@akeneo.com"
	git config --global user.name "pim_ci_instance_creation"
	cd $(CLOUD_CUSTOMERS_DEV_DIR) && git add $(INSTANCE_CLOUD_CUSTOMERS_DEV_DIR) && git commit -m "Created instance $(PFID)"
	(cd $(CLOUD_CUSTOMERS_DEV_DIR) && git status -uall -s && git pull --rebase  && git push) || \
	bash $(DEPLOYMENT_DIR)/bin/pull_push_loop.sh $(CLOUD_CUSTOMERS_DEV_DIR)

.PHONY: uncommit-instance
uncommit-instance: pull-cloud-customer-dev
	# Check if files were previously commited
ifeq (,$(wildcard ${INSTANCE_CLOUD_CUSTOMERS_DEV_DIR}/main.tf.json))
	@echo "WARN: $(INSTANCE_CLOUD_CUSTOMERS_DEV_DIR)/main.tf.json doesn't exist and need to be created"
	make create-ci-release-files
	make delete
else
	@echo "INFO: $(INSTANCE_CLOUD_CUSTOMERS_DEV_DIR)/main.tf.json already exist -> Copying files to INSTANCE_DIR"
	cp -r $(INSTANCE_CLOUD_CUSTOMERS_DEV_DIR) $(INSTANCE_DIR)
	make delete
	git config --global user.email "pim_ci@akeneo.com"
	git config --global user.name "pim_ci_instance_deletion"
	cd $(CLOUD_CUSTOMERS_DEV_DIR) && git rm -rf $(INSTANCE_CLOUD_CUSTOMERS_DEV_DIR) && git commit -m "Deleted instance $(PFID)"
	(cd $(CLOUD_CUSTOMERS_DEV_DIR) && git status -uall -s && git pull --rebase && git push) || \
	bash $(DEPLOYMENT_DIR)/bin/pull_push_loop.sh $(CLOUD_CUSTOMERS_DEV_DIR)
endif

.PHONY: upgrade-instance
upgrade-instance:
	git clone --depth 1 git@operation-tools:akeneo/operation-tools.git $(OPERATIONS_TOOLS_DIR)
	cd $(OPERATIONS_TOOLS_DIR) && git checkout $(OPERATIONS_TOOLS_BRANCH)
	cp $(OPERATIONS_TOOLS_DIR)/jenkins.yaml /usr/share/jenkins/ref/casc/jenkins.yaml
	mkdir -p /workspace && cp ${HOME}/gcloud-service-key.json /workspace/pim_ci.json
	JENKINS_LIBS_SSH_PRIVATE_FILE_PATH=~/.ssh/id_rsa_2c6118646e36aa7476fd5e6b735923c6 \
	PERRYBOT_SSH_PRIVATE_FILE_PATH=~/.ssh/id_rsa_5f7bb3cbd43de2c2365f9db487865f67 \
	DEVTEST=true \
	HELM_DEBUG=true \
	DEVTEST_INSTANCE=$(PFID) \
	JENKINSFILE_PATH=$(OPERATIONS_TOOLS_DIR)/saas-instances-upgrade.Jenkinsfile \
	/app/bin/jenkinsfile-runner-launcher -ns -u \
	-a "batchMode=false" \
	-a "skipShutdown=true" \
	-a "autoApply=true" \
	-a "release=$(IMAGE_TAG)" \
	-a "productTypePrefixFilter=$(TYPE)" \
	-a "googleProjectIdFilter=akecld-saas-dev" \
	-a "googleCloudZoneFilter=*" \
	-a "forceUpdate=true"

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

.PHONY: release
release:
	TYPE=$(TYPE) bash $(DEPLOYMENT_DIR)/bin/release.sh $(OLD_IMAGE_TAG) $(NEW_IMAGE_TAG)

.PHONY: slack_helpdesk
slack_helpdesk:
ifeq ($(TYPE),srnt)
	curl -X POST -H 'Content-type: application/json' --data '{"text":"Serenity env has been deployed with the last tag $(IMAGE_TAG) : https://pimci-helpdesk.preprod.cloud.akeneo.com"}' $${SLACK_URL_HELPDESK};
endif
ifeq ($(TYPE),grth)
	curl -X POST -H 'Content-type: application/json' --data '{"text":"Growth env has been deployed with the last tag $(IMAGE_TAG) : https://pimci-helpdesk-ge.preprod.cloud.akeneo.com"}' $${SLACK_URL_HELPDESK};
endif

.PHONY: delete_pr_environments_hourly
delete_pr_environments_hourly:
	@echo "Deprecated"
	ENV_NAME=${ENV_NAME} bash $(DEPLOYMENT_DIR)/bin/remove_instances.sh

.PHONY: hourly_cleanup
hourly_cleanup: delete_environments_hourly remove_unused_resources

.PHONY: delete_environments_hourly
delete_environments_hourly:
	ENV_NAME=${ENV_NAME} TYPE=${TYPE} bash $(DEPLOYMENT_DIR)/bin/remove_instances.sh

.PHONY: delete_environment_manually
delete_environment_manually:
ifeq ($(NS),)
	@echo "ERR : You must choose a namespace to delete"
	exit 1
endif
	NS=$(NS) ENV_NAME=$(ENV_NAME) TYPE=$(shell echo "$(NS)" | cut -d "-" -f1) bash $(DEPLOYMENT_DIR)/bin/remove_instances.sh

# Deprecated
delete_expired_uptime_check:
	cd $(DEPLOYMENT_DIR)/bin/uptime && docker-compose run --rm composer composer install
	cd $(DEPLOYMENT_DIR)/bin/uptime && CLOUDSDK_PYTHON=python2 LOG_LEVEL=info docker-compose run --rm php make deployment-uptime-clear

.PHONY: remove_unused_resources
remove_unused_resources: remove_unused_gcloud_dns remove_unused_gcloud_pubsub remove_unused_gcloud_bucket remove_unused_disk remove_unused_mailgun_credentials remove_unused_iam remove_unused_gcloud_logging_metrics

.PHONY: remove_unused_gcloud_dns
remove_unused_gcloud_dns:
	@echo "=========================================================="
	@echo "=                 Remove unused dns                      ="
	@echo "=========================================================="
	bash $(DEPLOYMENT_DIR)/bin/remove_unused_gcloud_dns.sh

.PHONY: remove_unused_gcloud_pubsub
remove_unused_gcloud_pubsub:
	@echo "=========================================================="
	@echo "=                Remove unused pubsub                    ="
	@echo "=========================================================="
	bash $(DEPLOYMENT_DIR)/bin/remove_unused_gcloud_pubsub.sh

.PHONY: remove_unused_gcloud_bucket
remove_unused_gcloud_bucket:
	@echo "=========================================================="
	@echo "=                Remove unused bucket                    ="
	@echo "=========================================================="
	bash $(DEPLOYMENT_DIR)/bin/remove_unused_gcloud_bucket.sh

.PHONY: remove_unused_disk
remove_unused_disk: remove_unused_kube_disk remove_unused_gcloud_disk

.PHONY: remove_unused_kube_disk
remove_unused_kube_disk:
	@echo "=========================================================="
	@echo "=              Remove unused kube disk                   ="
	@echo "=========================================================="
	bash $(DEPLOYMENT_DIR)/bin/remove_unused_kube_disk.sh

.PHONY: remove_unused_gcloud_disk
remove_unused_gcloud_disk:
	@echo "=========================================================="
	@echo "=             Remove unused gcloud disk                  ="
	@echo "=========================================================="
	CLOUDSDK_CORE_DISABLE_PROMPTS=1 bash $(DEPLOYMENT_DIR)/bin/remove_unused_gcloud_disk.sh

.PHONY: remove_unused_mailgun_credentials
remove_unused_mailgun_credentials:
	@echo "=========================================================="
	@echo "=           Remove unused mailgun credentials            ="
	@echo "=========================================================="
	MAILGUN_API_KEY=${MAILGUN_API_KEY} bash $(PWD)/deployments/bin/remove_unused_mailgun_credentials.sh

.PHONY: remove_unused_iam
remove_unused_iam:
	@echo "=========================================================="
	@echo "=                   Remove unused IAM                    ="
	@echo "=========================================================="
	bash $(PWD)/deployments/bin/remove_unused_gcloud_iam.sh

.PHONY: remove_unused_gcloud_logging_metrics
remove_unused_gcloud_logging_metrics:
	@echo "=========================================================="
	@echo "=             Remove unused logging metrics              ="
	@echo "=========================================================="
	bash $(PWD)/deployments/bin/remove_unused_gcloud_logging_metrics.sh

.PHONY: clone_serenity
clone_serenity:
	@echo "Deprecated"
	PRODUCT_REFERENCE_TYPE=serenity_instance MAIN_TF_TEMPLATE=serenity_instance INSTANCE_NAME=${INSTANCE_NAME} IMAGE_TAG=$(IMAGE_TAG) INSTANCE_NAME_PREFIX=pimci-duplic make create-ci-release-files && \
	SOURCE_PFID=$(SOURCE_PFID) TYPE=$(TYPE) INSTANCE_NAME=$(INSTANCE_NAME) bash $(DEPLOYMENT_DIR)/bin/clone_saas_instance.sh

.PHONY: clone_saas_instance
clone_saas_instance:
	TYPE=$(TYPE) INSTANCE_NAME=${INSTANCE_NAME} IMAGE_TAG=$(IMAGE_TAG) INSTANCE_NAME_PREFIX=pimci-duplic make create-ci-release-files
	SOURCE_PFID=$(SOURCE_PFID) TYPE=$(TYPE) INSTANCE_NAME=$(INSTANCE_NAME) DESTINATION_PATH=$(INSTANCE_DIR) BUCKET=$(BUCKET) bash $(DEPLOYMENT_DIR)/bin/clone_saas_instance.sh

.PHONY: migrate_flexibility
migrate_flexibility:
	TYPE=$(TYPE) INSTANCE_NAME=${INSTANCE_NAME} IMAGE_TAG=$(IMAGE_TAG) INSTANCE_NAME_PREFIX=pimci-duplic make create-ci-release-files
	ENV_NAME=dev SOURCE_PFID=$(SOURCE_PFID) SOURCE_PROJECT_ID=$(SOURCE_PROJECT_ID) PED_TAG=$(IMAGE_TAG) INSTANCE_NAME=$(INSTANCE_NAME) BUCKET=$(BUCKET) bash $(DEPLOYMENT_DIR)/bin/migrate_flexibility.sh

.PHONY: migrate_flexibility_to_serenity
migrate_flexibility_to_serenity:
	TYPE=$(TYPE) INSTANCE_NAME=${INSTANCE_NAME} IMAGE_TAG=$(IMAGE_TAG) INSTANCE_NAME_PREFIX=pimci-duplic make create-ci-release-files
	ENV_NAME=dev SOURCE_PFID=$(SOURCE_PFID) SOURCE_PROJECT_ID=$(SOURCE_PROJECT_ID) PED_TAG=$(IMAGE_TAG) INSTANCE_NAME=$(INSTANCE_NAME) BUCKET=$(BUCKET) bash $(DEPLOYMENT_DIR)/bin/migrate_flexibility_to_serenity.sh

.PHONY: php-image-prod
php-image-prod: #Doc: build docker image for pim-enterprise-dev with the prod tag
	echo "${RELEASE_NAME}" > "$(PIM_SRC_PATH)/version.txt"
	DOCKER_BUILDKIT=1 docker build --no-cache --progress=plain --pull --tag eu.gcr.io/akeneo-ci/pim-enterprise-dev:${IMAGE_TAG} --target prod --build-arg COMPOSER_AUTH='${COMPOSER_AUTH}' -f $(PIM_SRC_PATH)/Dockerfile ${PIM_ROOT_DIR}

.PHONY: push-php-image-prod
push-php-image-prod: #Doc: push docker image to docker hub
	docker push eu.gcr.io/akeneo-ci/pim-enterprise-dev:${IMAGE_TAG}

.PHONY: test-helm-cronjob
test-helm-cronjob: #Doc: Test declared cronjob job are available via the PIM console
	bash $(DEPLOYMENT_DIR)/bin/test-cronjob-values.sh

.PHONY: test_helm_generated_k8s_files
test_helm_generated_k8s_files: #Doc Test helm generated templates are K8S compliant
	bash $(DEPLOYMENT_DIR)/bin/test_helm_generated_k8s_files.sh
