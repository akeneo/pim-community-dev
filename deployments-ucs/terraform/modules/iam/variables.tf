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