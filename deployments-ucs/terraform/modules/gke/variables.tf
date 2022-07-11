variable "project" {
  description = "Project to deploy to"
  type        = string
}

variable "host_project_id" {
  description = "project_id of the host project which host the shared VPC"
  type        = string
}

variable "shared_vpc_name" {
  description = "Name of the shared VPC"
  type        = string
}

variable "regions" {
  description = "List of regions where gke is deployed"
  type        = list(string)
  default = [
    "europe-west-2",
    "europe-west-3",
    "us-central-1",
    "asia-east-2",
    "australia-southeast-1",
  ]
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

variable "node_pool_configs" {
  description = "List of the configs of node pools"
  type        = list(map(string))
  default = [
    {
      name              = "default"
      preemptible       = false
      machine_type      = "n1-standard-16"
      min_node_count    = 1
      max_node_count    = 60
      max_pods_per_node = 64
    }
  ]
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


variable "cluster_developers" {
  type        = list(string)
  description = "List of GKE resource editors"
  default     = []
}

variable "viewer_members" {
  type = list(string)
  default = []
}

variable "admin_members" {
  type = list(string)
  default = []
}

variable "default_region" {
  type = string
  default = "europe-west1"
}
