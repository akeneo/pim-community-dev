output "id" {
  description = "an identifier for the resource with format projects/{{project}}/locations/{{location}}/functions/{{name}}"
  value       = join("", google_cloudfunctions2_function.this[*].id)
}

output "environment" {
  description = "The environment the function is hosted on"
  value = join("", google_cloudfunctions2_function.this[*].environment)
}

output "state" {
  description = "Describes the current state of the function"
  value = join("", google_cloudfunctions2_function.this[*].state)
}

output "update_time" {
  description = "The last update timestamp of a Cloud Function"
  value = join("", google_cloudfunctions2_function.this[*].update_time)
}

output "uri" {
  description = "The function uri"
  value = join("", google_cloudfunctions2_function.this[*].service_config[0].uri)
}
