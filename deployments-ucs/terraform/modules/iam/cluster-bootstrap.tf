resource "google_project_iam_custom_role" "helm_admin_role" {
  project     = var.project_id
  role_id     = "pim.saas.clusteradmin.role"
  title       = "GKE cluster roles admin"
  description = "Role for managing GKE clusterroles and bindings"
  permissions = [
    "container.roles.create",
    "container.roles.delete",
    "container.roles.update",
    "container.roles.bind",

    "container.roleBindings.create",
    "container.roleBindings.delete",
    "container.roleBindings.update",

    "container.clusterRoles.create",
    "container.clusterRoles.delete",
    "container.clusterRoles.update",
    "container.clusterRoles.bind",

    "container.clusterRoleBindings.create",
    "container.clusterRoleBindings.delete",
    "container.clusterRoleBindings.update",

    "container.mutatingWebhookConfigurations.create",
    "container.mutatingWebhookConfigurations.delete",
    "container.mutatingWebhookConfigurations.update",

    "container.validatingWebhookConfigurations.create",
    "container.validatingWebhookConfigurations.delete",
    "container.validatingWebhookConfigurations.update",

    "container.podSecurityPolicies.create",
    "container.podSecurityPolicies.delete",
    "container.podSecurityPolicies.update",
  ]
}

resource "google_project_iam_custom_role" "cloud_build_role" {
  project     = var.project_id
  role_id     = "pim.saas.cloudbuild.role"
  title       = "Cloud build roles admin"
  description = "Role for managing cloud build bindings"
  permissions = [
    "logging.buckets.write",
    "logging.logEntries.create",

    "cloudbuild.builds.get",
    "cloudbuild.builds.list",
  ]
}

resource "google_service_account" "cluster_bootstrap" {
  project      = var.project_id
  account_id   = "cluster-bootstrap"
  display_name = "Cluster bootstrap service account"
}

resource "google_project_iam_binding" "helm_admin_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.helm_admin_role.name

  members = [
    "serviceAccount:${google_service_account.cluster_bootstrap.email}",
  ]
}

resource "google_project_iam_member" "helm_dev_binding" {
  project = var.project_id
  role    = "roles/container.developer"
  member  = "serviceAccount:${google_service_account.cluster_bootstrap.email}"
}

resource "google_project_iam_binding" "cloud_build_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.cloud_build_role.name

  members = [
    "serviceAccount:${google_service_account.cluster_bootstrap.email}",
  ]
}

resource "google_service_account_iam_binding" "cluster_bootstrap_sa_cloudbuild_binding" {
  service_account_id = google_service_account.cluster_bootstrap.name
  role               = "roles/iam.serviceAccountTokenCreator"

  members = [
    "serviceAccount:service-${data.google_project.current.number}@gcp-sa-cloudbuild.iam.gserviceaccount.com",
  ]
}

resource "google_project_iam_member" "cluster_bootstrap_sa_cloudbuild_admin_binding" {
  project = var.project_id
  role    = "roles/iam.serviceAccountUser"
  member  = "serviceAccount:${google_service_account.cluster_bootstrap.email}"
}
