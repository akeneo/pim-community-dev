# Force bash compatibility (Instead of user default shell)
SHELL := /bin/bash

INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)

.PHONY: helm-prepare
helm-prepare:
	helm init --client-only
	helm repo add $(HELM_REPO_PROD) gs://$(HELM_REPO_PROD)/
	helm repo update

.PHONY: create-main-tf-for-pim3-with-last-tag
create-main-tf-for-pim3-with-last-tag:
	export GIT_SSH_COMMAND='ssh -i ~/.ssh/id_rsa_1f25f8bb595295f6e2f2972f30d4e966 -o UserKnownHostsFile=~/.ssh/known_hosts -o IdentitiesOnly=Yes' && \
	RELEASE_TO_DEPLOY=$$(git ls-remote --tags --sort="version:refname" git@github.com:akeneo/pim-enterprise-cloud | grep -oE 'v3\.2\.[0-9]+-[0-9]{2}$$' | tail -n1); \
	echo $${RELEASE_TO_DEPLOY}; \
	PEC_TAG=$${RELEASE_TO_DEPLOY} make create-main-tf-for-pim3

.PHONY: create-main-tf-for-pim3
create-main-tf-for-pim3:
	@echo $(PEC_TAG);
	mkdir -p $(DEPLOYMENTS_INSTANCES_DIR)/3.2/
	CLUSTER_DNS_NAME=$(CLUSTER_DNS_NAME) \
	GOOGLE_CLUSTER_ZONE=$(GOOGLE_CLUSTER_ZONE) \
	GOOGLE_MANAGED_ZONE_DNS=$(GOOGLE_MANAGED_ZONE_DNS) \
	GOOGLE_MANAGED_ZONE_NAME=$(GOOGLE_MANAGED_ZONE_NAME) \
	GOOGLE_PROJECT_ID=$(GOOGLE_PROJECT_ID) \
	INSTANCE_NAME=$(INSTANCE_NAME) \
	PEC_TAG=$(PEC_TAG) \
	PFID=$(PFID) \
	envsubst < $(PWD)/deployments/config/serenity_32_instance.tpl.tf > $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf

.PHONY: create-pimyaml-for-pim3
create-pimyaml-for-pim3:
	cp deployments/config/ci-values_32.yaml $(DEPLOYMENTS_INSTANCES_DIR)/3.2/values.yaml
	yq w -i $(DEPLOYMENTS_INSTANCES_DIR)/3.2/values.yaml pim.defaultCatalog icecat
	cat $(PIM_SRC_DIR)/deployments/config/catalog-3.2.yaml >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/values.yaml

.PHONY: deploy-pim3
deploy-pim3: create-main-tf-for-pim3-with-last-tag terraform-init-for-pim3 create-pimyaml-for-pim3 terraform-apply-for-pim3

.PHONY: terraform-init-for-pim3
terraform-init-for-pim3:
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && export GIT_SSH_COMMAND='ssh -i ~/.ssh/id_rsa_1f25f8bb595295f6e2f2972f30d4e966 -o UserKnownHostsFile=~/.ssh/known_hosts -o IdentitiesOnly=Yes' && terraform init $(TF_INPUT_FALSE) -upgrade

.PHONY: terraform-apply-for-pim3
terraform-apply-for-pim3:
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && TF_LOG=TRACE TF_LOG_PATH=terraform.log terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: terraform-destroy-for-pim3
terraform-destroy-for-pim3:
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: terraform-pre-upgrade-disk
terraform-pre-upgrade-disk:
	# Required by https://github.com/akeneo/pim-enterprise-dev/pull/8599
	yq d -i $(INSTANCE_DIR)/values.yaml "mysql.common.persistentDisks"
	for PD in $$(kubectl get -n $(PFID) pv $$(kubectl get -n $(PFID) pvc -l role=mysql-server -o jsonpath='{.items[*].spec.volumeName}') -o jsonpath='{..spec.gcePersistentDisk.pdName}'); do \
		yq w -i $(INSTANCE_DIR)/values.yaml "mysql.common.persistentDisks[+]" "$${PD}"; \
	done

.PHONY: terraform-pre-upgrade
terraform-pre-upgrade: terraform-init terraform-pre-upgrade-disk
	# Move monitoring resources from pim to pim-monitoring
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_logging_metric.login_count module.pim-monitoring.google_logging_metric.login_count
	sleep 1
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_logging_metric.login-response-time-distribution module.pim-monitoring.google_logging_metric.login-response-time-distribution
	sleep 1
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_logging_metric.logs-count module.pim-monitoring.google_logging_metric.logs-count
	sleep 1
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_monitoring_alert_policy.alert_policy module.pim-monitoring.google_monitoring_alert_policy.alert_policy
	sleep 1
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_monitoring_notification_channel.pagerduty module.pim-monitoring.google_monitoring_notification_channel.pagerduty
	sleep 1
	cd $(INSTANCE_DIR) && terraform state mv module.pim.google_monitoring_uptime_check_config.https module.pim-monitoring.google_monitoring_uptime_check_config.https
	# Delete useless resources
	cd $(INSTANCE_DIR) && terraform state rm module.pim.template_file.metric-template
	sleep 1
	cd $(INSTANCE_DIR) && terraform state rm module.pim.local_file.metric-rendered
	sleep 1
	cd $(INSTANCE_DIR) && terraform state rm module.pim.null_resource.metric
	sleep 1
