INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)

PHONY: create-main-tf-for-pim3-with-last-tag
create-main-tf-for-pim3-with-last-tag:
	git clone git@github.com:akeneo/pim-enterprise-cloud.git; \
	RELEASE_TO_DEPLOY=$$(cd pim-enterprise-cloud; git describe --tags $$(git rev-list --tags --max-count=1)); \
	echo $${RELEASE_TO_DEPLOY}; \
	PEC_TAG=$${RELEASE_TO_DEPLOY} make create-main-tf-for-pim3

PHONY: create-main-tf-for-pim3
create-main-tf-for-pim3:
	@echo $(PEC_TAG);
	mkdir -p ~/3.2/
	@echo "terraform {" >> ~/3.2/main.tf
	@echo "backend \"gcs\" {" >> ~/3.2/main.tf
	@echo "bucket  = \"akecld-terraform\"" >> ~/3.2/main.tf
	@echo "prefix  = \"saas/$(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)/$(PFID)/\"" >> ~/3.2/main.tf
	@echo "}" >> ~/3.2/main.tf
	@echo "}" >> ~/3.2/main.tf
	@echo "module \"pim\" {" >> ~/3.2/main.tf
	@echo "source = \"git@github.com:akeneo/pim-enterprise-cloud.git//infra/terraform?ref=$(PEC_TAG)\"" >> ~/3.2/main.tf
	@echo "google_project_id                 = \"$(GOOGLE_PROJECT_ID)\"" >> ~/3.2/main.tf
	@echo "google_project_zone                 = \"$(GOOGLE_CLUSTER_ZONE)\"" >> ~/3.2/main.tf
	@echo "instance_name                       = \"$(INSTANCE_NAME)\"" >> ~/3.2/main.tf
	@echo "dns_external                        = \"$(INSTANCE_NAME).$(GOOGLE_MANAGED_ZONE_DNS).\"" >> ~/3.2/main.tf
	@echo "dns_internal                        = \"$(CLUSTER_DNS_NAME)\"" >> ~/3.2/main.tf
	@echo "dns_zone                            = \"$(GOOGLE_MANAGED_ZONE_NAME)\"" >> ~/3.2/main.tf
	@echo "pager_duty_service_key              = \"d55f85282a8e4e16b2c822249ad440bd\"" >> ~/3.2/main.tf
	@echo "google_storage_location             = \"eu\"" >> ~/3.2/main.tf
	@echo "papo_project_code                   = \"NOT_ON_PAPO_$(PFID)\"" >> ~/3.2/main.tf
	@echo "force_destroy_storage               = true" >> ~/3.2/main.tf
	@echo "}" >> ~/3.2/main.tf

PHONY: create-pimyaml-for-pim3
create-pimyaml-for-pim3:
	cp ~/project/deployments/config/ci-values_32.yaml ~/3.2/values.yaml
	yq w -i ~/3.2/values.yaml pim.defaultCatalog icecat
	cat ~/project/deployments/config/catalog-3.2.yaml >> ~/3.2/values.yaml

.PHONY: deploy-pim3
deploy-pim3: create-main-tf-for-pim3-with-last-tag terraform-init-for-pim3 create-pimyaml-for-pim3 terraform-apply-for-pim3

.PHONY: terraform-init-for-pim3
terraform-init-for-pim3:
	cd ~/3.2 && terraform init $(TF_INPUT_FALSE) -upgrade

.PHONY: terraform-apply-for-pim3
terraform-apply-for-pim3:
	cd ~/3.2 && terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)
	cd ~/3.2 && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE) -target=module.pim.datadog_synthetics_test.test_browser

.PHONY: terraform-destroy-for-pim3
terraform-destroy-for-pim3:
	cd ~/3.2 && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)
