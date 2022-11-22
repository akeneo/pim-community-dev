variable "project" {
  description = "Project wich will hosts the clusters"
  type        = string
}

variable "name" {
  description = "Name of the cluster"
  type        = string
  default     = null
}

variable "host_project_id" {
  description = "project_id of the host project which host the shared VPC"
  type        = string
}

variable "shared_vpc_name" {
  description = "Name of the shared VPC"
  type        = string
}

variable "region" {
  description = "Region where gke is deployed"
  type        = string
}

variable "enable_master_global_access" {
  description = "True if the master can be accessed from internet"
  default     = true
  type        = bool
}

variable "enable_gke_backup" {
  description = "True if the gke backup is enabled"
  default     = true
  type        = bool
}

variable "enable_config_connector" {
  description = "True if the managed config connector is enabled"
  default     = true
  type        = bool
}

variable "node_pool_configs" {
  description = "Map of the configs of node pools"
  type        = map(map(string))
  default = {
    "default" = {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    }
  }
}

variable "node_pool_labels" {
  description = "Map of labels to apply on node pools"
  type        = map(map(string))
  default = {
    "default" = {
      "node-type" = "default"
    }
  }
}

variable "node_pool_tags" {
  description = "Map of network tags to apply on node pools"
  type        = map(list(string))
  default = {
    "default" = []
  }
}

variable "node_pools_taints" {
  type        = map(list(object({ key = string, value = string, effect = string })))
  description = "Map of lists containing node taints by node-pool name"
  default = {
    default = []
  }
}

variable "node_locations" {
  description = "Map of zone location to place nodes of node pools by region"
  type        = map(map(list(string)))
  default     = null
}

variable "cluster_developers" {
  type        = list(string)
  description = "List of GKE resource editors"
  default     = []
}

variable "viewer_members" {
  type    = list(string)
  default = []
}

variable "admin_members" {
  type    = list(string)
  default = []
}

variable "default_region" {
  type    = string
  default = "europe-west1"
}

variable "gke_sa_email" {
  type        = string
  description = "Email of the gke service account"
}

variable "min_master_version" {
  type        = string
  description = "Minimum control plane version"
  default     = "latest"
}

variable "master_ipv4_cidr_block" {
  type        = string
  description = "Master subnet, to be removed"
  default     = null
}

variable "master_ipv4_cidr_block_name" {
  type        = string
  description = "Master subnet name"
  default     = "gke"
}

variable "maintenance_policy" {
  type = object({
    start_time = string
    end_time   = string
    recurence  = string
  })
  description = "Maintenance policy"
  default     = null
}
