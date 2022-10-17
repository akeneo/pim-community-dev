resource "google_service_account" "cluster_bootstrap" {
  project      = var.project_id
  account_id   = "cluster-bootstrap"
  display_name = "Cluster bootstrap service account"
}

resource "google_project_iam_custom_role" "helm_admin_role" {
  project     = var.project_id
  role_id     = "pim.saas.clusteradmin.role"
  title       = "GKE cluster roles admin"
  description = "Role for managing GKE clusterroles and bindings"
  permissions = [
    "container.clusterRoles.bind",
    "container.clusterRoles.create",
    "container.clusterRoles.delete",
    "container.clusterRoles.escalate",
    "container.clusterRoles.update",

    "container.clusterRoleBindings.create",
    "container.clusterRoleBindings.delete",
    "container.clusterRoleBindings.update",

    "container.mutatingWebhookConfigurations.create",
    "container.mutatingWebhookConfigurations.delete",
    "container.mutatingWebhookConfigurations.update",

    "container.podSecurityPolicies.create",
    "container.podSecurityPolicies.delete",
    "container.podSecurityPolicies.update",

    "container.roles.bind",
    "container.roles.create",
    "container.roles.delete",
    "container.roles.escalate",
    "container.roles.update",

    "container.roleBindings.create",
    "container.roleBindings.delete",
    "container.roleBindings.update",

    "container.validatingWebhookConfigurations.create",
    "container.validatingWebhookConfigurations.delete",
    "container.validatingWebhookConfigurations.update",
  ]
}

resource "google_project_iam_custom_role" "resource_manager_project_reader_role" {
  project     = var.project_id
  role_id     = "resource_project_reader.role"
  title       = "Resource manager project reader"
  description = "Role for managing resource project reader"
  permissions = [
    "resourcemanager.projects.get",
  ]
}

resource "google_project_iam_binding" "helm_admin_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.helm_admin_role.name

  members = [
    "serviceAccount:${google_service_account.cluster_bootstrap.email}",
  ]
}

resource "google_project_iam_binding" "resource_manager_project_reader_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.resource_manager_project_reader_role.name

  members = [
    "serviceAccount:${google_service_account.cluster_bootstrap.email}",
  ]
}

resource "google_project_iam_member" "helm_dev_binding" {
  project = var.project_id
  role    = "roles/container.developer"
  member  = "serviceAccount:${google_service_account.cluster_bootstrap.email}"
}

resource "google_service_account_iam_binding" "cluster_bootstrap_sa_cloudbuild_binding" {
  service_account_id = google_service_account.cluster_bootstrap.name
  role               = "roles/iam.serviceAccountTokenCreator"

  members = [
    "serviceAccount:service-${data.google_project.current.number}@gcp-sa-cloudbuild.iam.gserviceaccount.com",
    "serviceAccount:main-service-account@akecld-prd-pim-saas-shared.iam.gserviceaccount.com"
  ]
}
