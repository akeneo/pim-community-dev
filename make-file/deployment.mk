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

.PHONY: prepare-infrastructure-artifacts
prepare-infrastructure-artifacts: render-helm-templates dump-kubernetes-namespace
	cd $(INSTANCE_DIR) ;\
	mkdir -p ~/artifacts/infra ;\
	rm -Rf $(INSTANCE_DIR)/.terraform ;\
	mv $(INSTANCE_DIR) ~/artifacts/infra/

.PHONY: render-helm-templates
render-helm-templates:
	cd $(INSTANCE_DIR) ;\
	mkdir -p helm-render ;\
	helm template .terraform/modules/pim/pim -f tf-helm-pim-values.yaml -f values.yaml -n $(PFID) --output-dir helm-render

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
	done

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
create-ci-release-files: create-ci-values create-pim-main-tf create-serenity-commons-tf

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
	touch $(INSTANCE_DIR)/values.yaml
	@echo $(INSTANCE_NAME_PREFIX)
	@echo "terraform {" > $(INSTANCE_DIR)/main.tf
	@echo "backend \"gcs\" {" >> $(INSTANCE_DIR)/main.tf
	@echo "bucket  = \"akecld-terraform-dev\"" >> $(INSTANCE_DIR)/main.tf
	@echo "prefix  = \"saas/$(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)/$(PFID)/\"" >> $(INSTANCE_DIR)/main.tf
	@echo "}" >> $(INSTANCE_DIR)/main.tf
	@echo "}" >> $(INSTANCE_DIR)/main.tf
	@echo "module \"pim\" {" >> $(INSTANCE_DIR)/main.tf
	@echo "source = \"$(PIM_SRC_DIR)/deployments/terraform\"" >> $(INSTANCE_DIR)/main.tf
	@echo "google_project_id                   = \"$(GOOGLE_PROJECT_ID)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "google_project_zone                 = \"$(GOOGLE_CLUSTER_ZONE)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "instance_name                       = \"$(INSTANCE_NAME)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "dns_external                        = \"$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS).\"" >> $(INSTANCE_DIR)/main.tf
	@echo "dns_internal                        = \"$(CLUSTER_DNS_NAME)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "dns_zone                            = \"$(GOOGLE_MANAGED_ZONE_NAME)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "google_storage_location             = \"eu\"" >> $(INSTANCE_DIR)/main.tf
	@echo "papo_project_code                   = \"NOT_ON_PAPO_$(PFID)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "force_destroy_storage               = true" >> $(INSTANCE_DIR)/main.tf
	@echo "pim_version                         = \"$(IMAGE_TAG)\"" >> $(INSTANCE_DIR)/main.tf
	@echo "}" >> $(INSTANCE_DIR)/main.tf

.PHONY: create-serenity-commons-tf
create-serenity-commons-tf: $(INSTANCE_DIR)
	@echo "provider \"google\" {" > $(INSTANCE_DIR)/commons_serenity.tf
	@echo "  version = \"~> 3.17.0\"" >> $(INSTANCE_DIR)/commons_serenity.tf
	@echo "}" >> $(INSTANCE_DIR)/commons_serenity.tf

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
	kubectl get ns|grep "srnt-pimci-pr"; \
	for namespace in $$(kubectl get ns|grep "srnt-pimci-pr"|awk '{print $$1}'); do \
		NS_INFO=$$(kubectl get ns |grep $${namespace}); \
		NS_STATUS=$$(echo $${NS_INFO}|awk '{print $$2}'); \
		NS_AGE=$$(echo $${NS_INFO}|awk '{print $$3}'); \
		INSTANCE_NAME=$$(echo $${namespace} | awk -F'srnt-pimci-pr-' '{print $$NF}'); \
		echo "---[INFO] namespace $${namespace} with status $${NS_STATUS} since $${NS_AGE} (instance_name=$${INSTANCE_NAME})"; \
		if [[ "$${NS_AGE}" == *24* ]] ; then \
			echo "Environment will be deleted !"; \
		else \
			continue; \
		fi; \
		INSTANCE_NAME=pimci-pr-$${INSTANCE_NAME} IMAGE_TAG=$${CIRCLE_SHA1} make create-ci-release-files && \
		INSTANCE_NAME_PREFIX=pimci-pr IMAGE_TAG=$${INSTANCE_NAME} make delete; \
		echo "---[DELETED] namespace $${namespace}"; \
	done

.PHONY: terraform-pre-upgrade
terraform-pre-upgrade: terraform-init
	yq d -i $(INSTANCE_DIR)/values.yaml "mysql.common.persistentDisks"
	for PD in $$(kubectl get -n $(PFID) pv $$(kubectl get -n $(PFID) pvc -l role=mysql-server -o jsonpath='{.items[*].spec.volumeName}') -o jsonpath='{..spec.gcePersistentDisk.pdName}'); do \
		yq w -i $(INSTANCE_DIR)/values.yaml "mysql.common.persistentDisks[+]" "$${PD}"; \
	done
