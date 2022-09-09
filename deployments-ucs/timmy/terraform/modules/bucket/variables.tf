variable "project_id" {
  description = "The ID of the project in which the resource belongs"
  type        = string
}

variable "name" {
  description = "The name of the bucket"
  type        = string
}

variable "location" {
  description = "The GCS location"
  type        = string
}

variable "force_destroy" {
  description = "When deleting a bucket, this boolean option will delete all contained objects. If you try to delete a bucket that contains objects, Terraform will fail that run"
  type        = bool
  default     = false
}

variable "uniform_bucket_level_access" {
  description = "Enables Uniform bucket-level access access to a bucket"
  type        = bool
}

variable "storage_class" {
  description = "The Storage Class of the new bucket"
  type = string
  default = "STANDARD"
}

variable "versioning" {
  description = "While set to true, versioning is fully enabled for this bucket"
  type = bool
}
