variable "project_id" {
  type        = string
  description = "Default Project ID"
}

variable "trigger_name" {
  type        = string
  description = "Name for the trigger (should be unique in the same cloud build project)"
}

variable "env" {
  type = string
}

variable "region" {
  type = string
}

variable "cloudbuild_filename" {
  type        = string
  description = "Path, from the source root, to a file whose contents is used for the template."
}

variable "cloudbuild_included_files" {
  type        = list(string)
  description = "IncludedFiles are file glob matches using https://golang.org/pkg/path/filepath/#Match extended with support for **"
  default     = []
}

variable "cloudbuild_github_org" {
  type        = string
  description = "Github organisation"
  default     = "akeneo"
}

variable "cloudbuild_github_repository" {
  type        = string
  description = "Github repository"
}

variable "cloudbuild_github_branch" {
  type        = string
  description = "Github branch"
}

variable "cloudbuild_service_account" {
  type        = string
  description = "The service account used for all user-controlled operations including triggers.patch, triggers.run, builds.create, and builds.cancel."
}

variable "target_project_id" {
  type = string
}

variable "impersonate" {
  type    = string
  default = "main_service_account"
}
