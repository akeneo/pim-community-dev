# Adapted CircleCi Jobs (CI=true) OR manual run from release dir

# Force bash compatibility (Instead of user default shell)
SHELL := /bin/bash

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
	cd $(INSTANCE_DIR) && TF_LOG=TRACE TF_LOG_PATH=terraform.log terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: prepare-infrastructure-artifacts
prepare-infrastructure-artifacts: render-helm-templates dump-kubernetes-namespace
	mkdir -p ~/artifacts/infra
	cp -raT $(DEPLOYMENTS_INSTANCES_DIR) ~/artifacts/infra/ || true
	rm -Rf ~/artifacts/infra/**/.terraform || true
	rm -Rf ~/artifacts/infra/**/.kubeconfig || true

.PHONY: render-helm-templates
render-helm-templates:
	cd $(INSTANCE_DIR) ;\
	mkdir -p helm-render ;\
	helm template .terraform/modules/pim/pim -f tf-helm-pim-values.yaml -f values.yaml -n $(PFID) --output-dir helm-render || true
ifeq ($(INSTANCE_NAME_PREFIX),pimup)
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 ;\
	mkdir -p helm-render ;\
	helm template .terraform/modules/pim/pim -f tf-helm-pim-values.yaml -f values.yaml -n $(PFID) --output-dir helm-render || true
endif

# TODO
.PHONY: dump-kubernetes-namespace
dump-kubernetes-namespace:
	cd $(INSTANCE_DIR) ;\
	mkdir -p k8s-dump ;\
	RESOURCES=$$(kubectl -n $(PFID) get all -o jsonpath='{range .items[*]}{.kind}{"\n"}{end}' | uniq) ;\
	for RESOURCE in $${RESOURCES};do \
		echo $${RESOURCE} \
		# RESOURCE_NAMES=$$(kubectl --context $${CONTEXT} -n $(PFID) get -o json $${RESOURCE}|jq '.items[].metadata.name'|sed "s/\"//g") ;\
		# for RESOURCE_NAME in ${RESOURCE_NAMES};do \
		# 	mkdir -p "k8s-dump" ;\
		# 	kubectl --context $${CONTEXT} -n $(PFID) get -o yaml $${RESOURCE} $${RESOURCE_NAME} > "k8s-dump/$${RESOURCE_NAME}.yaml" ;\
		# done ;\
	done || true

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
	if [ -f "$(INSTANCE_DIR)/main.tf" ]; then \
		cd $(INSTANCE_DIR) ;\
		echo "Destroying $(INSTANCE_DIR) ..." ;\
		terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE) ;\
	elif [ -f "$(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf" ]; then \
		cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 ;\
		echo "Destroying $(DEPLOYMENTS_INSTANCES_DIR)/3.2 ..." ;\
		terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE) ;\
	fi

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
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.intermediateUpgrades[+] "v20200211172331"
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.intermediateUpgrades[+] "v20200401020139"
endif
ifeq ($(INSTANCE_NAME),pimci-helpdesk)
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.installPim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradePim.enabled true
	yq w -i $(INSTANCE_DIR)/values.yaml pim.hook.upgradeES.enabled false
endif
ifeq ($(INSTANCE_NAME_PREFIX),pimci-pr)
	sed 's/^\(FLAG_.*_ENABLED\).*/  \1: "1"/g' .env | (grep "FLAG_.*_ENABLED" || true) >> $(PIM_SRC_DIR)/deployments/terraform/pim/templates/env-configmap.yaml
endif

.PHONY: create-pim-main-tf
create-pim-main-tf: $(INSTANCE_DIR)
	CLUSTER_DNS_NAME=$(CLUSTER_DNS_NAME) \
	GOOGLE_CLUSTER_ZONE=$(GOOGLE_CLUSTER_ZONE) \
	GOOGLE_MANAGED_ZONE_DNS=$(GOOGLE_MANAGED_ZONE_DNS) \
	GOOGLE_MANAGED_ZONE_NAME=$(GOOGLE_MANAGED_ZONE_NAME) \
	GOOGLE_PROJECT_ID=$(GOOGLE_PROJECT_ID) \
	IMAGE_TAG=$(IMAGE_TAG) \
	INSTANCE_NAME=$(INSTANCE_NAME) \
	PFID=$(PFID) \
	PIM_SRC_DIR=$(PIM_SRC_DIR) \
	envsubst < $(PWD)/deployments/config/serenity_instance.tpl.tf > $(INSTANCE_DIR)/main.tf

.PHONY: test-prod
test-prod:
	export KUBECONFIG=$(INSTANCE_DIR)/.kubeconfig
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
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make create-ci-release-files; \
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make deploy && \
	INSTANCE_NAME=pimci-helpdesk IMAGE_TAG=$${RELEASE_TO_DEPLOY} make slack_helpdesk

.PHONY: slack_helpdesk
slack_helpdesk:
	curl -X POST -H 'Content-type: application/json' --data '{"text":"Serenity env has been deployed with the last tag $(IMAGE_TAG) : https://pimci-helpdesk.preprod.cloud.akeneo.com"}' $${SLACK_URL_HELPDESK};

.PHONY: deploy_pr_environment
deploy_pr_environment:
	@PR_NUMBER=$${CIRCLE_PULL_REQUEST##*/} && \
	echo "This environment will be available at https://pimci-pr-$${PR_NUMBER}.$(GOOGLE_MANAGED_ZONE_DNS) once deployed :)"
	PR_NUMBER=$${CIRCLE_PULL_REQUEST##*/} && \
	INSTANCE_NAME_PREFIX=pimci-pr INSTANCE_NAME=pimci-pr-$${PR_NUMBER} IMAGE_TAG=$${CIRCLE_SHA1} make create-ci-release-files && \
	INSTANCE_NAME_PREFIX=pimci-pr INSTANCE_NAME=pimci-pr-$${PR_NUMBER} IMAGE_TAG=$${CIRCLE_SHA1} make deploy

.PHONY: delete_pr_environments
delete_pr_environments:
	bash $(PWD)/deployments/bin/remove_pr_instance.sh

.PHONY: terraform-pre-upgrade
terraform-pre-upgrade: terraform-init
	# Required by https://github.com/akeneo/pim-enterprise-dev/pull/8599
	yq d -i $(INSTANCE_DIR)/values.yaml "mysql.common.persistentDisks"
	for PD in $$(kubectl get -n $(PFID) pv $$(kubectl get -n $(PFID) pvc -l role=mysql-server -o jsonpath='{.items[*].spec.volumeName}') -o jsonpath='{..spec.gcePersistentDisk.pdName}'); do \
		yq w -i $(INSTANCE_DIR)/values.yaml "mysql.common.persistentDisks[+]" "$${PD}"; \
	done
	# Move monitoring resources from pim to pim-monitoring
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_logging_metric.login_count module.pim-monitoring.google_logging_metric.login_count
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_logging_metric.login-response-time-distribution module.pim-monitoring.google_logging_metric.login-response-time-distribution
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_logging_metric.logs-count module.pim-monitoring.google_logging_metric.logs-count
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_monitoring_alert_policy.alert_policy module.pim-monitoring.google_monitoring_alert_policy.alert_policy
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_monitoring_notification_channel.pagerduty module.pim-monitoring.google_monitoring_notification_channel.pagerduty
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_monitoring_uptime_check_config.https module.pim-monitoring.google_monitoring_uptime_check_config.https
