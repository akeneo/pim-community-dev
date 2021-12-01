//
// TOPICS
//

resource "google_pubsub_topic" "connector_bigcommerce" {
  count = 1
  name = "${local.pfid}-connector-bigcommerce"

  labels = {
    pfid = local.pfid
    topic_type     = "pim_job_connector"
    connector_name = "bigcommerce"
  }
}

//
// SUBSCRIPTIONS
//

resource "google_pubsub_subscription" "connector_bigcommerce" {
  count = 1
  name  = "${local.pfid}-connector-bigcommerce"
  topic = google_pubsub_topic.connector_bigcommerce[0].name

  ack_deadline_seconds = 600
  expiration_policy {
    ttl = ""
  }
  // 604800s = 7 days
  message_retention_duration = "604800s"

  labels = {
    pfid = local.pfid
    subscription_type = "pim_job_connector"
    connector_name = "bigcommerce"
  }

  enable_message_ordering = true
}

//
// PERMISSIONS
//
//
// We do declare 2 kinds of applications, the one that writes and the one that
// reads. It gives rights to the same service account: PIM.

resource "google_pubsub_topic_iam_member" "pubsub_publisher_connector_bigcommerce" {
  count = 1
  topic  = google_pubsub_topic.connector_bigcommerce[0].name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.connector_bigcommerce,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_subscriber_connector_bigcommerce" {
  count = 1
  subscription = google_pubsub_subscription.connector_bigcommerce[0].name
  role         = "roles/pubsub.subscriber"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.connector_bigcommerce,
  ]
}

