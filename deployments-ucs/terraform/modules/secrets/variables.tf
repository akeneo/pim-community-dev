variable "project_id" {}

variable "secrets" {
  type = list(object({
    name    = string,
    members = list(string),
    labels  = map(string),
  }))
}
