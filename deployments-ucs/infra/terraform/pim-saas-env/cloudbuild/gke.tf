locals {
  gke_cloudbuild_filename = ".cloudbuild/infra/akecld-prd-pim-saas-terraform-gke.yaml"
  gke_cloudbuild_included_files = [
    ".cloudbuild/infra/akecld-prd-pim-saas-terraform-gke.yaml",
    "deployments-ucs/infra/terraform/${var.env}/**",
    "deployments-ucs/infra/terraform/modules/**",
    "deployments-ucs/infra/terraform/pim-saas-env/gke/**"
  ]
  gke_overrides = {
    "australia-southeast1" = {
      name = "${var.project_id}-aust-southeast1"
    }
  }
}

module "cloudbuild_trigger_gke_plan" {
  source                    = "../../modules/cloudbuild-infra"
  for_each                  = toset(var.regions)
  approval_required         = false
  cloudbuild_filename       = local.gke_cloudbuild_filename
  cloudbuild_included_files = local.gke_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "region:${each.key}", "action:plan"]
  trigger_name              = "terraform-gke-plan-${var.env}-${each.key}"
  trigger_on_pr             = true
  trigger_on_push           = false
  substitutions = {
    _MODULE             = "gke"
    _ENV                = var.env
    _REGION             = each.key
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/gke/${each.key}"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = false
    _CLUSTER_NAME       = lookup(lookup(local.gke_overrides, each.key, {}), "name", "${var.project_id}-${each.key}")
    _PIM_DEPLOYER_IMAGE = local.pim_deployer_image
  }
}

module "cloudbuild_trigger_gke_apply" {
  source                    = "../../modules/cloudbuild-infra"
  for_each                  = toset(var.regions)
  approval_required         = true
  cloudbuild_filename       = local.gke_cloudbuild_filename
  cloudbuild_included_files = local.gke_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:terraform", "env:${var.env}", "region:${each.key}", "action:apply"]
  trigger_name              = "terraform-gke-apply-${var.env}-${each.key}"
  trigger_on_pr             = false
  trigger_on_push           = true
  substitutions = {
    _MODULE             = "gke"
    _ENV                = var.env
    _REGION             = each.key
    _TF_BUCKET          = var.tf_bucket
    _BACKEND_PREFIX     = "infra/pim-saas/${var.project_id}/gke/${each.key}"
    _GOOGLE_PROJECT_ID  = var.project_id
    _TARGET_IMPERSONATE = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _TF_APPLY           = true
    _CLUSTER_NAME       = lookup(lookup(local.gke_overrides, each.key, {}), "name", "${var.project_id}-${each.key}")
    _PIM_DEPLOYER_IMAGE = local.pim_deployer_image
  }
}
