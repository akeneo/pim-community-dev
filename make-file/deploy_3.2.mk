INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)

PHONY: create-main-tf-for-pim3-with-last-tag
create-main-tf-for-pim3-with-last-tag:
	rm -Rf pim-enterprise-cloud; git clone git@github.com:akeneo/pim-enterprise-cloud.git; \
	RELEASE_TO_DEPLOY=$$(cd pim-enterprise-cloud; git describe --tags $$(git rev-list --tags --max-count=1)); \
	echo $${RELEASE_TO_DEPLOY}; \
	PEC_TAG=$${RELEASE_TO_DEPLOY} make create-main-tf-for-pim3

PHONY: create-main-tf-for-pim3
create-main-tf-for-pim3:
	@echo $(PEC_TAG);
	mkdir -p $(DEPLOYMENTS_INSTANCES_DIR)/3.2/
	@echo "terraform {" > $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "backend \"gcs\" {" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "bucket  = \"akecld-terraform-dev\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "prefix  = \"saas/$(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)/$(PFID)/\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "}" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "}" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "module \"pim\" {" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "source = \"git@github.com:akeneo/pim-enterprise-cloud.git//infra/terraform?ref=$(PEC_TAG)\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "google_project_id                 = \"$(GOOGLE_PROJECT_ID)\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "google_project_zone                 = \"$(GOOGLE_CLUSTER_ZONE)\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "instance_name                       = \"$(INSTANCE_NAME)\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "dns_external                        = \"$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS).\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "dns_internal                        = \"$(CLUSTER_DNS_NAME)\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "dns_zone                            = \"$(GOOGLE_MANAGED_ZONE_NAME)\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "pager_duty_service_key              = \"d55f85282a8e4e16b2c822249ad440bd\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "google_storage_location             = \"eu\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "papo_project_code                   = \"NOT_ON_PAPO_$(PFID)\"" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "force_destroy_storage               = true" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf
	@echo "}" >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/main.tf

PHONY: create-pimyaml-for-pim3
create-pimyaml-for-pim3:
	cp deployments/config/ci-values_32.yaml $(DEPLOYMENTS_INSTANCES_DIR)/3.2/values.yaml
	yq w -i $(DEPLOYMENTS_INSTANCES_DIR)/3.2/values.yaml pim.defaultCatalog icecat
	cat $(PIM_SRC_DIR)/deployments/config/catalog-3.2.yaml >> $(DEPLOYMENTS_INSTANCES_DIR)/3.2/values.yaml

.PHONY: deploy-pim3
deploy-pim3: create-main-tf-for-pim3-with-last-tag terraform-init-for-pim3 create-pimyaml-for-pim3 terraform-apply-for-pim3

.PHONY: terraform-init-for-pim3
terraform-init-for-pim3:
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && terraform init $(TF_INPUT_FALSE) -upgrade

.PHONY: terraform-apply-for-pim3
terraform-apply-for-pim3:
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: terraform-destroy-for-pim3
terraform-destroy-for-pim3:
	cd $(DEPLOYMENTS_INSTANCES_DIR)/3.2 && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)
