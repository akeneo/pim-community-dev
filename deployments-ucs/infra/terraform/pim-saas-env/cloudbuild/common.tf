locals {
  common_cloudbuild_filename = ".cloudbuild/infra/akecld-prd-pim-saas-terraform.yaml"
  common_cloudbuild_destroy  = ".cloudbuild/infra/akecld-prd-pim-saas-terraform-destroy.yaml"
  common_cloudbuild_included_files = [
    ".cloudbuild/infra/akecld-prd-pim-saas-terraform.yaml",
    "deployments-ucs/infra/terraform/pim-saas-env/cloudbuild/common.tf",
    "deployments-ucs/infra/terraform/${var.env}/**",
    "deployments-ucs/infra/terraform/modules/**",
    "deployments-ucs/infra/terraform/pim-saas-env/common/**"
  ]
}

module "cloudbuild_trigger_common_plan" {
  source                    = "../../modules/cloudbuild-infra"
  approval_required         = false
  cloudbuild_filename       = local.common_cloudbuild_filename
  cloudbuild_included_files = local.common_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "action:plan"]
  trigger_name              = "terraform-common-plan-${var.env}"
  trigger_on_pr             = true
  trigger_on_push           = false
  substitutions = {
    _MODULE             = "common"
    _ENV                = var.env
    _REGION             = "global"
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/common"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = false
  }
}

module "cloudbuild_trigger_common_apply" {
  source                    = "../../modules/cloudbuild-infra"
  approval_required         = true
  cloudbuild_filename       = local.common_cloudbuild_filename
  cloudbuild_included_files = local.common_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "action:apply"]
  trigger_name              = "terraform-common-apply-${var.env}"
  trigger_on_pr             = false
  trigger_on_push           = true
  substitutions = {
    _MODULE             = "common"
    _ENV                = var.env
    _REGION             = "global"
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/common"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = true
  }
}

module "cloudbuild_trigger_common_destroy" {
  source                    = "../../modules/cloudbuild-infra"
  disabled                  = true
  approval_required         = true
  cloudbuild_filename       = local.common_cloudbuild_destroy
  cloudbuild_included_files = []
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "action:destroy"]
  trigger_name              = "terraform-common-destroy-${var.env}"
  trigger_on_pr             = false
  trigger_on_push           = true
   substitutions = {
    _MODULE             = "common"
    _ENV                = var.env
    _REGION             = "global"
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/common"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = true
  }
}
