SHELL := env bash

HELM_REPO ?= akeneo-charts-dev
HELM_URL :=  gs://$(HELM_REPO)/
HELM_CHART_NAME := pim
HELM_CHART_VERSION ?= 0.0.0-0
HELM_TIMEOUT=900
HELM_VALUES_DIR:=${CURDIR}/values
CUSTOMERS_DIR?=../../cloud-customers/

DEBUG ?=--debug 

.PHONY: helm-init
helm-init:
	helm init --client-only
	helm plugin install https://github.com/nouney/helm-gcs --version 0.2.0 || true
	helm repo add akeneo-charts-dev gs://akeneo-charts-dev/
	helm repo add akeneo-charts gs://akeneo-charts/

.PHONY: helm-lint
helm-lint: 
	helm lint  ./$(HELM_CHART_NAME)/

.PHONY: helm-build
helm-build: helm-lint
ifeq ($(FORCE), true)
	$(eval helm_force=--force)
endif
	helm repo update
	helm package -u ./$(HELM_CHART_NAME)/ --version $(HELM_CHART_VERSION) $(DEBUG)
	helm gcs push ./$(HELM_CHART_NAME)-$(HELM_CHART_VERSION).tgz $(HELM_REPO) $(helm_force)

.PHONY: helm-template
helm-template:
	helm template ./$(HELM_CHART_NAME)/ > "template_$(HELM_CHART_NAME)"

.PHONY: helm-install
helm-install: terraform-apply
	@helm fetch $(HELM_REPO)/$(HELM_CHART_NAME) --version $(HELM_CHART_VERSION)
	@echo -e == Install or update PIM ==
	[[ -f "$(HELM_VALUES_DIR)/pim-saas-$(ENV_NAME).yaml" ]] && helm_value="-f $(HELM_VALUES_DIR)/pim-saas-$(ENV_NAME).yaml" || helm_value="" ; \
	[[ ! -z "$(PIM_IMAGE_VERSION)" ]] && echo "image.pim.tag=$(PIM_IMAGE_VERSION)" && helm_value+=" --set image.pim.tag=$(PIM_IMAGE_VERSION)"; \
	[[ ! -z "$(PIM_IMAGE_REPO)" ]] && echo "image.pim.repository=$(PIM_IMAGE_REPO)" && helm_value+=" --set image.pim.repository=$(PIM_IMAGE_REPO)"; \
	cluster_name=$$($(TERRAFORM) output cluster_name); \
	env_name=$$($(TERRAFORM) output env_name); \
	release_file="$(CUSTOMERS_DIR)saas/env/$${env_name}-env/$(PROJECT_NAME)/$${cluster_name}/srnt-releases/$(PFID)/srnt.yaml"; \
	[[ -f "$${release_file}" ]] && srnt_value="-f $${release_file}" || srnt_value="" ; \
	helm upgrade --install --wait --timeout $(HELM_TIMEOUT) srnt-$(PFID) $(HELM_REPO)/$(HELM_CHART_NAME) --version $(HELM_CHART_VERSION) --namespace srnt-$(PFID) -f ./terraform/pim-master-values.yaml $${helm_value} $${srnt_value}

.PHONY: helm-test
helm-test: terraform-apply
	helm test --timeout $(HELM_TIMEOUT) srnt-$(PFID) || kubectl logs $(PFID)-auth-test --namespace srnt-$(PFID)

.PHONY: helm-delete
helm-delete: terraform/kubeconfig
	helm delete --purge --timeout $(HELM_TIMEOUT) srnt-$(PFID)
	# workaround to clean https://github.com/kubernetes/helm/issues/4019
	kubectl delete all --all -n srnt-$(PFID) --force --grace-period=0
	kubectl delete ns srnt-$(PFID)

##########################
## Terrafom targets
##########################

TERRAFORM := cd ./terraform && terraform
export KUBECONFIG=./terraform/kubeconfig

.PHONY: create-tfvars
create-tfvars: # Not using a file target allow to recreate file "./terraform/terraform.tfvars" each time
	@echo 'google_project_name="$(PROJECT_NAME)"' > ./terraform/terraform.tfvars
	@echo 'pfid="$(PFID)"' >> ./terraform/terraform.tfvars

check_var = $(if $(strip $(shell echo "$2")),,$(error "$1" is not defined))

.PHONY: check-input
check-input: # Check cluster input
	$(call check_var,PROJECT_NAME,$(PROJECT_NAME))
	$(call check_var,PFID,$(PFID))

.PHONY: terraform-init 
terraform-init: check-input create-tfvars
	@$(TERRAFORM) init -backend-config="prefix=saas/$(PROJECT_NAME)/pim-$(PFID)"

.PHONY: terraform-kubeconfig
terraform-kubeconfig: check-input create-tfvars terraform-init
ifeq ($(FORCE), true)
	$(eval terrafrom_param=-auto-approve)
endif
	@$(TERRAFORM) apply --target=local_file.kubeconfig $(terrafrom_param) 

.PHONY: terraform-apply
terraform-apply: terraform-init
ifeq ($(FORCE), true)
	$(eval terrafrom_param=-auto-approve)
endif
	@$(TERRAFORM) apply $(terrafrom_param) 

terraform/kubeconfig: terraform-init
	@$(TERRAFORM) apply --target=local_file.kubeconfig -auto-approve

.PHONY: terraform-destroy
terraform-destroy: terraform-init
ifeq ($(FORCE), true)
	find $(MODULE_DIR) -type f -print | xargs sed -i -E "s/(prevent_destroy\s+=\s+\"?)true(\"?)/\1false\2/g"
	find $(MODULE_DIR) -type f -print | xargs sed -i -E "s/(force_destroy\s+=\s+\"?)false(\"?)/\1true\2/g"
	$(TERRAFORM) plan -destroy
	$(TERRAFORM) destroy -auto-approve
	find $(MODULE_DIR) -type f -print | xargs sed -i -E "s/(prevent_destroy\s+=\s+\"?)false(\"?)/\1true\2/g"
	find $(MODULE_DIR) -type f -print | xargs sed -i -E "s/(force_destroy\s+=\s+\"?)true(\"?)/\1false\2/g"
else 
	$(TERRAFORM) destroy
endif
	rm ./terraform/terraform.tfvars
