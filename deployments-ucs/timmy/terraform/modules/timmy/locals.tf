locals {
  suffix         = var.branch_name == "master" ? "" : "-${var.suffix_name}"
  suffix_name    = local.suffix == "-" ? "" : local.suffix

  name_max_chars = 63

  bucket_shorted     = "bck"
  bucket_prefix_name = "${var.region_prefix}-${local.bucket_shorted}-timmy"
  bucket_name        = substr("${local.bucket_prefix_name}${local.suffix_name}", 0, local.name_max_chars)

  cloud_scheduler_http_method          = "POST"
  cloud_scheduler_shorted              = "csch"
  cloud_scheduler_prefix_name          = "${var.region_prefix}-${local.cloud_scheduler_shorted}-timmy"
  cloud_scheduler_name_max_chars       = 63
  cloud_scheduler_request_portal_name  = substr("${local.cloud_scheduler_prefix_name}-request-portal${local.suffix_name}", 0, local.name_max_chars)
  cloud_scheduler_clean_firestore_name = substr("${local.cloud_scheduler_prefix_name}-clean-firestore${local.suffix_name}", 0, local.name_max_chars)

  cloud_function_node_env = "production"
  cloud_function_shorted  = "cfun"
  cloud_function_labels   = merge(var.cloud_function_labels, {
    application    = "timmy"
    branch_name    = var.branch_name
    region         = var.region
    repository_url = "pim-enterprise-dev"
  })
  cloud_function_service_account_email = "timmy-cloud-function@${var.project_id}.iam.gserviceaccount.com"
  cloud_function_prefix_name           = "${var.region_prefix}-${local.cloud_function_shorted}-timmy"
  cloud_function_name_max_chars        = 63
  cloud_function_clean_firestore_name  = substr("${local.cloud_function_prefix_name}-clean-firestore${local.suffix_name}", 0, local.name_max_chars)
  cloud_function_create_doc_name       = substr("${local.cloud_function_prefix_name}-create-doc${local.suffix_name}", 0, local.name_max_chars)
  cloud_function_create_tenant_name    = substr("${local.cloud_function_prefix_name}-create-tenant${local.suffix_name}", 0, local.name_max_chars)
  cloud_function_delete_doc_name       = substr("${local.cloud_function_prefix_name}-delete-doc${local.suffix_name}", 0, local.name_max_chars)
  cloud_function_delete_tenant_name    = substr("${local.cloud_function_prefix_name}-delete-tenant${local.suffix_name}", 0, local.name_max_chars)
  cloud_function_request_portal_name   = substr("${local.cloud_function_prefix_name}-request-portal${local.suffix_name}", 0, local.name_max_chars)

  argocd_url                  = "https://argocd-${var.region}.${var.domain}"
  argocd_username             = "admin"
  argocd_password_secret_name = "${upper(replace(var.region, "-", "_"))}_ARGOCD_PASSWORD"
}
