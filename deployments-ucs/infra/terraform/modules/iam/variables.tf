variable "project_id" {
  description = "Project wich will hosts the service accounts"
  type        = string
}
variable "source_project_id" {
  description = "Project wich hosts the legacy tenants. Used for migration from legacy to UCS."
  type        = list(string)
  default     = []
}

variable "firestore_projects_id" {
  description = "List of Firestore projects id"
  type        = list(string)
  default = [
    "akecld-prd-pim-fire-eur-dev",
    "akecld-prd-pim-fire-eur-us",
  ]
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

variable "external_dns_k8s_ns" {
  description = "External DNS kubernetess namespace"
  type        = string
  default     = "external-dns"
}

variable "external_dns_k8s_sa" {
  description = "External DNS kubernetess service account name"
  type        = string
  default     = "external-dns"
}

variable "custom_metrics_k8s_ns" {
  description = "custom_metrics kubernetess namespace"
  type        = string
  default     = "custom-metrics"
}

variable "custom_metrics_k8s_sa" {
  description = "custom_metrics kubernetess service account name"
  type        = string
  default     = "custom-metrics-stackdriver-adapter"
}

variable "ci_service_account" {
  description = "Service account used by CI (access to secrets)"
  type        = string
  default     = "main-service-account"
}
