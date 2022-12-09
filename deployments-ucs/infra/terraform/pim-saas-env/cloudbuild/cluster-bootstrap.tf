locals {
  bootstrap_cloudbuild_filename = ".cloudbuild/clusters/akecld-prd-pim-saas-apps.yaml"
  bootstrap_cloudbuild_included_files = [
    ".cloudbuild/clusters/akecld-prd-pim-saas-apps.yaml",
    "deployments-ucs/infra/k8s/**",
  ]
}

module "cloudbuild_trigger_bootstrap_plan" {
  source                    = "../../modules/cloudbuild-infra"
  for_each                  = var.clusters
  approval_required         = false
  cloudbuild_filename       = local.bootstrap_cloudbuild_filename
  cloudbuild_included_files = local.bootstrap_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:helm", "env:${var.env}", "region:${each.value.region}", "action:plan"]
  trigger_name              = "terraform-bootstrap-plan-${var.env}-${each.value.region}"
  trigger_on_pr             = true
  trigger_on_push           = false
  substitutions = {
    _MODULE                  = "bootstrap"
    _ENV                     = var.env
    _REGION                  = each.value.region
    _GOOGLE_PROJECT_ID       = var.project_id
    _TARGET_IMPERSONATE      = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _APPLY                   = false
    _PIM_DEPLOYER_IMAGE      = local.pim_deployer_image
    _CLUSTER_NAME            = "${var.env_shorted}-${each.value.region_shorted}-gke-${var.project_name}-${each.value.cluster_number_region}"
    _ARGOCD_HELM_VERSION     = "5.8.2"
    _ARGOCD_APP_HELM_VERSION = "0.0.3"
  }
}

module "cloudbuild_trigger_bootstrap_apply" {
  source                    = "../../modules/cloudbuild-infra"
  for_each                  = var.clusters
  approval_required         = true
  cloudbuild_filename       = local.bootstrap_cloudbuild_filename
  cloudbuild_included_files = local.bootstrap_cloudbuild_included_files
  logs_bucket               = google_storage_bucket.cloudbuild_logs.url
  tags                      = ["type:helm", "env:${var.env}", "region:${each.value.region}", "action:apply"]
  trigger_name              = "terraform-bootstrap-apply-${var.env}-${each.value.region}"
  trigger_on_pr             = false
  trigger_on_push           = true
  substitutions = {
    _MODULE                  = "bootstrap"
    _ENV                     = var.env
    _REGION                  = each.value.region
    _GOOGLE_PROJECT_ID       = var.project_id
    _TARGET_IMPERSONATE      = "${var.impersonate}@${var.project_id}.iam.gserviceaccount.com"
    _APPLY                   = true
    _PIM_DEPLOYER_IMAGE      = local.pim_deployer_image
    _CLUSTER_NAME            = "${var.env_shorted}-${each.value.region_shorted}-gke-${var.project_name}-${each.value.cluster_number_region}"
    _ARGOCD_HELM_VERSION     = "5.8.2"
    _ARGOCD_APP_HELM_VERSION = "0.0.3"
  }
}
