variable "akeneo_connect_api_client_secret" {
  default = ""
  type    = string
}

variable "akeneo_connect_api_client_password" {
  default = ""
  type    = string
}

variable "akeneo_connect_saml_certificate" {
  default = ""
  type    = string
}

variable "akeneo_connect_saml_entity_id" {
  default = ""
  type    = string
}

variable "akeneo_connect_saml_sp_certificate_base64" {
  default = ""
  type    = string
}

variable "akeneo_connect_saml_sp_private_key_base64" {
  default = ""
  type    = string
}

variable "bucket_name" {
  default = ""
}

variable "chart_values_override_path" {
  type        = string
  default     = "values.yaml"
  description = "Path to helm chart values.yaml override, optional"
}

variable "dns_external" {
  type = string
}

variable "dns_internal" {
  type = string
}

variable "dns_project" {
  type    = string
  default = "akeneo-cloud"
}

variable "dns_zone" {
  type = string
}

variable "force_destroy_storage" {
  default = "false"
}

variable "ft_catalog_api_client_id" {
  default = ""
  type    = string
}

variable "ft_catalog_api_password" {
  default = ""
  type    = string
}

variable "ft_catalog_api_secret" {
  default = ""
  type    = string
}

variable "google_project_id" {
  type = string
}

variable "google_project_zone" {
  type        = string
  description = "Google zone where the project is deployed (Used for backup)."
}

variable "google_storage_location" {
  type = string
}

variable "instance_name" {
  type = string
}

variable "mailgun_api_key" {
  type = string
}

variable "mailgun_domain" {
  type    = string
  default = "mg.cloud.akeneo.com"
}

variable "mailgun_region" {
  default = "us"
}

variable "mailgun_host" {
  default = "smtp.mailgun.org"
}

variable "mailgun_port" {
  default = "2525"
}

variable "monitoring_authentication_token" {
  type        = string
  description = "Monitoring authentication token (Leave empty for auto-generated)"
  default     = ""
}

variable "mysql_disk_name" {
  type    = string
  default = ""
}

variable "mysql_disk_description" {
  type    = string
  default = ""
}

variable "mysql_disk_size" {
  type        = number
  description = "size in GB"
  default     = 100
}

variable "mysql_source_snapshot" {
  default = ""
  type    = string
}

variable "mysql_disk_type" {
  default = "pd-ssd"
  type    = string
}

variable "papo_project_code" {
  description = "Code of the project on Akeneo portal"
}

variable "pim_version" {
  default = ""
}

variable "product_reference_code" {
  type = string
}

variable "product_reference_type" {
  type = string
}

variable "product_reference_size" {
  type    = string
  default = "S"

  validation {
    condition     = can(regex("^(S|M|L|XL)|([1-9][0-9]*XL)$", var.product_reference_size))
    error_message = "Allowed values for product_reference_size are:\n- S\n- M\n- L\n- XL\n- 2XL\n- 3XL\n- ..."
  }
}

variable "type" {
  type    = string
  default = "srnt"
}

variable "types" {
  type = map
  default = {
    "serenity_instance"       = "srnt"
    "growth_edition_instance" = "grth"
    "pim_trial_instance"      = "tria"
  }
}

variable "use_edition_flag" {
  type        = bool
  default     = false
  description = "If set to true , it will hardcode srnt-.. in pfid"
}
