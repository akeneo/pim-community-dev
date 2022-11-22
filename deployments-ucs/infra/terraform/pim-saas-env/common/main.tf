module "iam" {
  source                = "../../modules/iam"
  project_id            = var.project_id
  firestore_projects_id = [for loc, proj in var.firestore_locations : proj]
  secrets_admins        = var.admins
  cloudbuild_admins     = var.admins
}

module "firestore" {
  source        = "../../modules/firestore"
  for_each      = var.firestore_locations
  location_id   = each.key
  project_id    = each.value
  database_type = "CLOUD_FIRESTORE"
}

module "secrets" {
  source     = "../../modules/secrets"
  project_id = var.project_id
  secrets = [
    {
      name = "ARGOCD_USERNAME"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]

      labels = {
        usage = "argocd"
      }
    },
    {
      name = "ARGOCD_PASSWORD"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ],

      labels = {
        usage = "argocd"
      }
    },
    {
      name = "MAILER_API_KEY"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "timmy-tenant-provisioning"
      }
    },
    {
      name = "TIMMY_PORTAL"
      members = [
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "timmy-portal-auth"
      }
    },
    {
      name = "TENANT_CONTEXT_ENCRYPTION_KEY"
      members = [
        "serviceAccount:${module.iam.pim_sa_email}",
        "serviceAccount:${module.iam.portal_function_sa_email}"
      ]
      labels = {
        usage = "tenant-context-encryption-key"
      }
    }
  ]
}

module "tenant_context_encryption_key" {
  source    = "../../modules/tenant-context-secret"
  secret_id = module.secrets.google_secrets_ids["TENANT_CONTEXT_ENCRYPTION_KEY"]
}

module "public_dns" {
  source     = "../../modules/public-dns"
  project_id = var.project_id
  zone_name  = var.public_zone

  forward = {
    target_project_id = var.shared_project_id
    target_zone_name  = var.shared_zone_name
  }
}

module "private_dns" {
  source     = "../../modules/private-dns"
  project_id = var.project_id
  zone_name  = var.private_zone
  networks   = []
  #networks   = [data.google_compute_network.vpc.id]
}

module "cloudarmor" {
  source     = "../../modules/cloudarmor"
  project_id = var.project_id

  enable_rate_limiting_api = false
}

terraform {
  backend "gcs" {}
  required_version = "= 1.1.3"
}
