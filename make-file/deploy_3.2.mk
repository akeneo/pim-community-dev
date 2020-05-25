# Force bash compatibility (Instead of user default shell)
SHELL := /bin/bash
# Usefull in order to retrieve env variable in sub shells
.EXPORT_ALL_VARIABLES:

INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)

PHONY: create-main-tf-for-pim3-with-last-tag
create-main-tf-for-pim3-with-last-tag:
	export GIT_SSH_COMMAND='ssh -i ~/.ssh/id_rsa_1f25f8bb595295f6e2f2972f30d4e966 -o UserKnownHostsFile=~/.ssh/known_hosts -o IdentitiesOnly=Yes' && \
	RELEASE_TO_DEPLOY=$$(git ls-remote --tags --sort="version:refname" git@github.com:akeneo/pim-enterprise-cloud | grep -oE 'v3\.2\.[0-9]+-[0-9]{2}$$' | tail -n1); \
	echo $${RELEASE_TO_DEPLOY}; \
	PEC_TAG=$${RELEASE_TO_DEPLOY} make create-main-tf-for-pim3

PHONY: create-main-tf-for-pim3
create-main-tf-for-pim3:
	@echo $(PEC_TAG);
	mkdir -p $(DEPLOYMENTS_INSTANCES_DIR)/3.2/
	envsubst < $(PWD)/deployments/config/serenity_32_instance.tpl.tf > $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf

PHONY: create-pimyaml-for-pim3
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
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: terraform-destroy-for-pim3
terraform-destroy-for-pim3:
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)
