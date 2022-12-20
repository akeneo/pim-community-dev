locals {
  gke_cloudbuild_filename = ".cloudbuild/infra/akecld-prd-pim-saas-terraform-gke.yaml"
  gke_cloudbuild_destroy  = ".cloudbuild/infra/akecld-prd-pim-saas-terraform-destroy.yaml"
  gke_cloudbuild_included_files = [
    ".cloudbuild/infra/akecld-prd-pim-saas-terraform-gke.yaml",
    "deployments-ucs/infra/terraform/pim-saas-env/cloudbuild/gke.tf",
    "deployments-ucs/infra/terraform/${var.env}/**",
    "deployments-ucs/infra/terraform/modules/**",
    "deployments-ucs/infra/terraform/pim-saas-env/gke/**"
  ]
}

module "cloudbuild_trigger_gke_plan" {
  source                    = "../../modules/cloudbuild-infra"
  for_each                  = var.clusters
  approval_required         = false
  cloudbuild_filename       = local.gke_cloudbuild_filename
  cloudbuild_included_files = local.gke_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "region:${each.value.region}", "action:plan"]
  trigger_name              = "terraform-gke-plan-${var.env}-${each.value.region}"
  trigger_on_pr             = true
  trigger_on_push           = false
  substitutions = {
    _MODULE             = "gke"
    _ENV                = var.env
    _ENV_SHORTED        = var.env_shorted
    _REGION             = each.value.region
    _REGION_SHORTED     = each.value.region_shorted
    _CLUSTER_NUMBER     = each.value.region_shorted
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/gke/${each.value.region}"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = false
    _CLUSTER_NAME       = "${var.env_shorted}-${each.value.region_shorted}-gke-${var.project_name}-${each.value.cluster_number_region}"
    _PIM_DEPLOYER_IMAGE = local.pim_deployer_image
  }
}

module "cloudbuild_trigger_gke_apply" {
  source                    = "../../modules/cloudbuild-infra"
  for_each                  = var.clusters
  approval_required         = true
  cloudbuild_filename       = local.gke_cloudbuild_filename
  cloudbuild_included_files = local.gke_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "region:${each.value.region}", "action:apply"]
  trigger_name              = "terraform-gke-apply-${var.env}-${each.value.region}"
  trigger_on_pr             = false
  trigger_on_push           = true
  substitutions = {
    _MODULE             = "gke"
    _ENV                = var.env
    _ENV_SHORTED        = var.env_shorted
    _REGION             = each.value.region
    _REGION_SHORTED     = each.value.region_shorted
    _CLUSTER_NUMBER     = each.value.region_shorted
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/gke/${each.value.region}"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = true
    _CLUSTER_NAME       = "${var.env_shorted}-${each.value.region_shorted}-gke-${var.project_name}-${each.value.cluster_number_region}"
    _PIM_DEPLOYER_IMAGE = local.pim_deployer_image
  }
}

module "cloudbuild_trigger_gke_destroy" {
  source                    = "../../modules/cloudbuild-infra"
  for_each                  = var.clusters
  disabled                  = true
  approval_required         = true
  cloudbuild_filename       = local.gke_cloudbuild_destroy
  cloudbuild_included_files = []
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "region:${each.value.region}", "action:destroy"]
  trigger_name              = "terraform-gke-destroy-${var.env}-${each.value.region}"
  trigger_on_pr             = false
  trigger_on_push           = true
  substitutions = {
    _MODULE             = "gke"
    _ENV                = var.env
    _ENV_SHORTED        = var.env_shorted
    _REGION             = each.value.region
    _REGION_SHORTED     = each.value.region_shorted
    _CLUSTER_NUMBER     = each.value.region_shorted
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/gke/${each.value.region}"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = true
    _CLUSTER_NAME       = "${var.env_shorted}-${each.value.region_shorted}-gke-${var.project_name}-${each.value.cluster_number_region}"
    _PIM_DEPLOYER_IMAGE = local.pim_deployer_image
  }
}
