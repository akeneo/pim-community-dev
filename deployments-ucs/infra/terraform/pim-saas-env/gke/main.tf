locals {
  gke_sa_email = var.gke_sa_email != null ? var.gke_sa_email : "gke-sa@${var.project_id}.iam.gserviceaccount.com"
}

module "gke" {
  source             = "../../modules/gke"
  name               = var.name
  project            = var.project_id
  host_project_id    = var.host_project_id
  shared_vpc_name    = var.shared_vpc_name
  gke_sa_email       = local.gke_sa_email
  region             = var.region
  cluster_developers = var.admins
  viewer_members     = var.viewers
  admin_members      = var.admins
  min_master_version = var.min_master_version

  node_pool_configs = {
    "default" = {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    },
    "mysql" = {
      name              = "mysql"
      preemptible       = false
      machine_type      = "n1-highmem-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    }
  }

  node_pool_labels = {
    "default" = {
      "node-type" = "default"
    },
    "mysql" = {
      component = "mysql",
      role      = "mysql-server"
    }
  }

  node_pools_taints = {
    default = [],
    mysql   = []
  }

  node_locations = {
    mysql = {
      "${var.region}" = formatlist("%s-%s", var.region, var.mysql_zones)
    }
  }
}

terraform {
  backend "gcs" {}
  required_version = "= 1.1.3"
}
