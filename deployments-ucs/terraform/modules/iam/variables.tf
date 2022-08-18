variable "project_id" {
  description = "Project wich will hosts the service accounts"
  type        = string
}

variable "crossplane_k8s_ns" {
  description = "Crossplane kubernetess namespace"
  type        = string
  default     = "crossplane-system"
}

variable "crossplane_k8s_sa" {
  description = "Crossplane kubernetess service account name"
  type        = string
  default     = "crossplane"
}

variable "secrets_admins" {
  description = "Secrets version managers"
  type        = list(string)
  default     = []
}

variable "cloudbuild_admins" {
  description = "Cloudbuild admins"
  type        = list(string)
  default     = []
}

variable "timmy_k8s_ns" {
  description = "Timmy deployment K8S namespace"
  type        = string
  default     = "timmy"
}

variable "timmy_k8s_sa" {
  description = "Timmy deployment K8S service account"
  type        = string
  default     = "timmy-deployment-sa"
}


variable "pim_k8s_ns" {
  description = "PIM deployment K8S namespace"
  type        = string
  default     = "pim-job"
}

variable "pim_k8s_sa" {
  description = "PIM deployment K8S service account"
  type        = string
  default     = "pim-deployment-sa"
}
