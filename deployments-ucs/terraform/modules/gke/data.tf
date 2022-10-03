data "google_project" "current" {
  project_id = var.project
}

data "google_compute_network" "shared_vpc" {
  project = var.host_project_id
  name    = var.shared_vpc_name
}

data "google_compute_subnetwork" "gke" {
  project = var.host_project_id
  region  = var.region
  name    = "${data.google_project.current.project_id}-${var.region}"
}

data "google_secret_manager_secret_version" "network_config" {
  project = var.host_project_id
  secret  = "network_${data.google_project.current.project_id}"
}
