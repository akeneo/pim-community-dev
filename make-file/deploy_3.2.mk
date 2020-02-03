PEC_TAG ?= v3.2.28-01
INSTANCE_NAME_PREFIX ?= pimci
INSTANCE_NAME ?= $(INSTANCE_NAME_PREFIX)-$(IMAGE_TAG)
PFID ?= srnt-$(INSTANCE_NAME)

PHONY: create-tf-files-for-pim3
create-tf-files-for-pim3:
	mkdir -p ~/3.2/
	@echo "terraform {" >> ~/3.2/main.tf
	@echo "backend \"gcs\" {" >> ~/3.2/main.tf
	@echo "bucket  = \"akecld-terraform\"" >> ~/3.2/main.tf
	@echo "prefix  = \"saas/$(GOOGLE_PROJECT_ID)/$(GOOGLE_CLUSTER_ZONE)/$(PFID)/\"" >> ~/3.2/main.tf
	@echo "project = \"akeneo-cloud\"" >> ~/3.2/main.tf
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
	cp ~/project/deployments/config/ci-values.yaml ~/3.2/pim.yaml
	yq w -i ~/3.2/pim.yaml pim.defaultCatalog icecat
	cat ~/project/deployments/config/catalog-3.2.yaml >> ~/3.2/pim.yaml

.PHONY: deploy-pim3
deploy-pim3: create-tf-files-for-pim3 terraform-init-for-pim3 create-pimyaml-for-pim3 terraform-apply-for-pim3 helm-deploy-for-pim3

.PHONY: terraform-init-for-pim3
terraform-init-for-pim3:
	cd ~/3.2 && terraform init $(TF_INPUT_FALSE) -upgrade

.PHONY: terraform-apply-for-pim3
terraform-apply-for-pim3:
	cd ~/3.2 && terraform apply $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: terraform-destroy-for-pim3
terraform-destroy-for-pim3:
	cd ~/3.2 && terraform destroy $(TF_INPUT_FALSE) $(TF_AUTO_APPROVE)

.PHONY: helm-deploy-for-pim3
helm-deploy-for-pim3:
	helm repo update
	cd ~/3.2 && helm upgrade --debug --wait --install --timeout 1500 srnt-${INSTANCE_NAME} \
				--namespace srnt-${INSTANCE_NAME} akeneo-charts/pim \
				--version $(PEC_TAG) -f tf-helm-pim-values.yaml \
				-f pim.yaml
