variable "project_id" {
  type        = string
  description = "Google project"
}

variable "enable_rate_limiting_api" {
  type    = bool
  default = true
}

variable "enable_rate_limiting_web" {
  type    = bool
  default = false
}

variable "rate_limit_api" {
  description = "Rate limit"
  type = object({
    count        = number
    interval_sec = number
  })
  default = {
    count        = 10000
    interval_sec = 60
  }
}

variable "rate_limit_web" {
  description = "Rate limit"
  type = object({
    count        = number
    interval_sec = number
  })
  default = {
    count        = 10000
    interval_sec = 60
  }
}
