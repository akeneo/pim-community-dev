//
// TOPICS
//

resource "google_pubsub_topic" "business-event" {
  name = "${local.pfid}-business-event"

  labels = {
    pfid = local.pfid
  }
}

resource "google_pubsub_topic" "job-queue" {
  name = "${local.pfid}-job-queue"

  labels = {
    pfid = local.pfid
  }
}

//
// SUBSCRIPTIONS
//

resource "google_pubsub_subscription" "webhook" {
  name                 = "${local.pfid}-webhook"
  topic                = google_pubsub_topic.business-event.name

  ack_deadline_seconds = 600
  expiration_policy {
    ttl = ""
  }
  message_retention_duration = "7200s"

  labels = {
    pfid = local.pfid
  }
}

resource "google_pubsub_subscription" "job-queue" {
  name                 = "${local.pfid}-job-queue"
  topic                = google_pubsub_topic.job-queue.name

  ack_deadline_seconds = 600
  expiration_policy {
    ttl = ""
  }
  message_retention_duration = "600s"

  labels = {
    pfid = local.pfid
  }
}

//
// PERMISSIONS
//
//
// We do declare 2 kinds of applications, the one that writes and the one that
// reads. It gives rights to the same service account: PIM.

resource "google_pubsub_topic_iam_member" "pubsub_publisher_business-event" {
  topic  = google_pubsub_topic.business-event.name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.business-event,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_publisher_job_queue" {
  topic  = google_pubsub_topic.job-queue.name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_viewer_job_queue" {
  topic  = google_pubsub_topic.job-queue.name
  role   = "roles/pubsub.viewer"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_viewer_business-event" {
  topic  = google_pubsub_topic.business-event.name
  role   = "roles/pubsub.viewer"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.business-event,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_subscriber_webhook" {
  subscription = google_pubsub_subscription.webhook.name
  role         = "roles/pubsub.subscriber"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.webhook,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_subscriber_job_queue" {
  subscription  = google_pubsub_subscription.job-queue.name
  role          = "roles/pubsub.subscriber"
  member        = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_viewer_webhook" {
  subscription = google_pubsub_subscription.webhook.name
  role         = "roles/pubsub.viewer"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.webhook,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_viewer_job_queue" {
  subscription  = google_pubsub_subscription.job-queue.name
  role          = "roles/pubsub.viewer"
  member        = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue,
  ]
}
