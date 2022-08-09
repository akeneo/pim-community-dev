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

resource "google_service_account" "helm_admin" {
  project      = var.project_id
  account_id   = "pim-saas-helm-admin"
  display_name = "PIM SaaS Helm admin"
}

resource "google_project_iam_binding" "helm_admin_binding" {
  project = var.project_id
  role    = google_project_iam_custom_role.helm_admin_role.name

  members = [
    "serviceAccount:${google_service_account.helm_admin.email}",
  ]
}

resource "google_project_iam_member" "helm_dev_binding" {
  project = var.project_id
  role    = "roles/container.developer"
  member  = "serviceAccount:${google_service_account.helm_admin.email}"
}
