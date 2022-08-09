module "cluster_bootstrap_sa" {
  project_id                  = var.project_id
  source                      = "./modules/cloudbuild-sa"
  account_id                  = "ucs-bootstrap"
  service_account_description = "UCS cluster bootstrap SA"
  admins                      = var.cloudbuild_admins
}

resource "google_project_iam_member" "sa_std_roles" {
  project = var.project_id
  role    = google_project_iam_custom_role.helm_admin_role.name
  member  = "serviceAccount:${module.cluster_bootstrap_sa.email}"
}
