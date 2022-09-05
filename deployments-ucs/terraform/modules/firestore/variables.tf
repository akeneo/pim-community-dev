variable "project_id" {
  type        = string
  description = "Project to deploy the fire store project"
}

variable "location_id" {
  type        = string
  description = "Target region for the firestore"
  default     = "europe-west"
}

