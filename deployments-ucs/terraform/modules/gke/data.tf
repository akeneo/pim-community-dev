data "google_project" "host_project" {
  project_id = var.host_project_id
}

data "google_project" "current" {
  project_id = var.project
}

data "google_compute_network" "shared_vpc" {
  project = var.host_project_id
  name    = var.shared_vpc_name
}

data "google_compute_subnetwork" "gke" {
  for_each = toset(var.regions)
  project  = var.host_project_id
  region   = each.key
  name     = "${data.google_project.current.project_id}-${each.value}"
}

data "google_secret_manager_secret_version" "network_config" {
  project = var.host_project_id
  secret  = "network_${data.google_project.current.project_id}"
}
