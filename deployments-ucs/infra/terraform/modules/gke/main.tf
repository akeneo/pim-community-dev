locals {
  network_data           = jsondecode(data.google_secret_manager_secret_version.network_config.secret_data)[var.region]
  master_ipv4_cidr_block = var.master_ipv4_cidr_block == null ? local.network_data.extra_ranges[var.master_ipv4_cidr_block_name] : var.master_ipv4_cidr_block
}

resource "google_project_iam_member" "gke_container_dev" {
  for_each = toset(var.cluster_developers)
  project  = var.project
  role     = "roles/container.admin"
  member   = each.value
}

#tfsec:ignore:google-gke-no-public-control-plane
#tfsec:ignore:google-gke-enable-network-policy
#tfsec:ignore:google-gke-enforce-pod-security-policy
resource "google_container_cluster" "gke" {
  project                  = var.project
  name                     = var.name
  location                 = var.region
  network                  = data.google_compute_network.shared_vpc.self_link
  subnetwork               = data.google_compute_subnetwork.gke.self_link
  networking_mode          = "VPC_NATIVE"
  initial_node_count       = 1
  remove_default_node_pool = true
  min_master_version       = var.min_master_version

  network_policy {
    enabled = false
  }

  ip_allocation_policy {
    cluster_secondary_range_name  = "pods"
    services_secondary_range_name = "services"
  }
  private_cluster_config {
    enable_private_nodes    = true
    enable_private_endpoint = false
    master_ipv4_cidr_block  = local.master_ipv4_cidr_block
    master_global_access_config {
      enabled = var.enable_master_global_access
    }
  }

  default_snat_status {
    disabled = true
  }

  master_authorized_networks_config {
    cidr_blocks {
      cidr_block = "0.0.0.0/0"
    }
  }

  pod_security_policy_config {
    enabled = "false"
  }

  workload_identity_config {
    workload_pool = "${data.google_project.current.project_id}.svc.id.goog"
  }

  addons_config {
    gke_backup_agent_config {
      enabled = var.enable_gke_backup
    }
    config_connector_config {
      enabled = var.enable_config_connector
    }
  }

  dynamic "maintenance_policy" {
    for_each = toset(var.maintenance_policy != null ? [true] : [])
    content {
      recurring_window {
        start_time = var.maintenance_policy.start_time
        end_time   = var.maintenance_policy.end_time
        recurrence = var.maintenance_policy.recurrence
      }
    }
  }

  timeouts {
    create = "30m"
    update = "40m"
  }

  provider = google-beta
}

resource "google_container_node_pool" "gke" {
  for_each           = var.node_pool_configs
  project            = var.project
  name               = "${each.value.name}-${var.region}"
  location           = var.region
  cluster            = google_container_cluster.gke.name
  initial_node_count = lookup(each.value, "autoscaling", true) ? lookup(each.value, "min_node_count", 1) : null
  node_count         = lookup(each.value, "autoscaling", true) ? null : lookup(each.value, "node_count", 1)
  max_pods_per_node  = lookup(each.value, "max_pods_per_node", 110)
  node_locations     = var.node_locations == null ? null : lookup(lookup(var.node_locations, each.value.name, tomap({})), var.region, null)

  node_config {
    preemptible     = lookup(each.value, "preemptible", false)
    machine_type    = lookup(each.value, "machine_type", "n1-standard-16")
    labels          = lookup(var.node_pool_labels, each.value.name, {})
    tags            = concat([data.google_project.current.project_id], lookup(var.node_pool_tags, each.key, []))
    service_account = var.gke_sa_email

    dynamic "taint" {
      for_each = var.node_pools_taints[each.value.name]
      content {
        effect = taint.value.effect
        key    = taint.value.key
        value  = taint.value.value
      }
    }

    oauth_scopes = [
      "https://www.googleapis.com/auth/cloud-platform"
    ]
    metadata = {
      "disable-legacy-endpoints" = true
    }

    workload_metadata_config {
      mode = "GKE_METADATA"
    }
  }

  dynamic "autoscaling" {
    for_each = lookup(each.value, "autoscaling", true) ? [each.value] : []
    content {
      min_node_count = lookup(autoscaling.value, "min_node_count", 1)
      max_node_count = lookup(autoscaling.value, "max_node_count", 60)
    }
  }
}
