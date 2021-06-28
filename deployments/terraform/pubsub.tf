//
// TOPICS
//

resource "google_pubsub_topic" "business-event" {
  name = "${local.pfid}-business-event"

  labels = {
    pfid = local.pfid
    topic_type = "pim_business_event"
  }
}

resource "google_pubsub_topic" "job-queue-ui" {
  name = "${local.pfid}-job-queue-ui"

  labels = {
    pfid = local.pfid
    topic_type = "pim_job_ui"
  }
}

resource "google_pubsub_topic" "job-queue-import-export" {
  name = "${local.pfid}-job-queue-import-export"

  labels = {
    pfid = local.pfid
    topic_type = "pim_job_import_export"
  }
}

resource "google_pubsub_topic" "job-queue-data-maintenance" {
  name = "${local.pfid}-job-queue-data-maintenance"

  labels = {
    pfid = local.pfid
    topic_type = "pim_job_data_maintenance"
  }
}

//
// SUBSCRIPTIONS
//

resource "google_pubsub_subscription" "webhook" {
  name  = "${local.pfid}-webhook"
  topic = google_pubsub_topic.business-event.name

  ack_deadline_seconds = 600
  expiration_policy {
    ttl = ""
  }
  message_retention_duration = "7200s"

  labels = {
    pfid = local.pfid
    subscription_type = "pim_webhook"
  }
}

resource "google_pubsub_subscription" "job-queue-ui" {
  name  = "${local.pfid}-job-queue-ui"
  topic = google_pubsub_topic.job-queue-ui.name

  ack_deadline_seconds = 600
  expiration_policy {
    ttl = ""
  }
  // 604800s = 7 days
  message_retention_duration = "604800s"

  labels = {
    pfid = local.pfid
    subscription_type = "pim_job_ui"
  }

  enable_message_ordering = true
}

resource "google_pubsub_subscription" "job-queue-import-export" {
  name  = "${local.pfid}-job-queue-import-export"
  topic = google_pubsub_topic.job-queue-import-export.name

  ack_deadline_seconds = 600
  expiration_policy {
    ttl = ""
  }
  // 604800s = 7 days
  message_retention_duration = "604800s"

  labels = {
    pfid = local.pfid
    subscription_type = "pim_job_import_export"
  }

  enable_message_ordering = true
}

resource "google_pubsub_subscription" "job-queue-data-maintenance" {
  name  = "${local.pfid}-job-queue-data-maintenance"
  topic = google_pubsub_topic.job-queue-data-maintenance.name

  ack_deadline_seconds = 600
  expiration_policy {
    ttl = ""
  }
  // 604800s = 7 days
  message_retention_duration = "604800s"

  labels = {
    pfid = local.pfid
    subscription_type = "pim_job_data_maintenance"
  }

  enable_message_ordering = true
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

resource "google_pubsub_topic_iam_member" "pubsub_publisher_job_queue_ui" {
  topic  = google_pubsub_topic.job-queue-ui.name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue-ui,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_publisher_job_queue_import_export" {
  topic  = google_pubsub_topic.job-queue-import-export.name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue-import-export,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_publisher_job_queue_data_maintenance" {
  topic  = google_pubsub_topic.job-queue-data-maintenance.name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue-data-maintenance,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_viewer_job_queue_ui" {
  topic  = google_pubsub_topic.job-queue-ui.name
  role   = "roles/pubsub.viewer"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue-ui,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_viewer_job_queue_import_export" {
  topic  = google_pubsub_topic.job-queue-import-export.name
  role   = "roles/pubsub.viewer"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue-import-export,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_viewer_job_queue_data_maintenance" {
  topic  = google_pubsub_topic.job-queue-data-maintenance.name
  role   = "roles/pubsub.viewer"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_topic.job-queue-data-maintenance,
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

resource "google_pubsub_subscription_iam_member" "pubsub_subscriber_job_queue_ui" {
  subscription = google_pubsub_subscription.job-queue-ui.name
  role         = "roles/pubsub.subscriber"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue-ui,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_subscriber_job_queue_import_export" {
  subscription = google_pubsub_subscription.job-queue-import-export.name
  role         = "roles/pubsub.subscriber"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue-import-export,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_subscriber_job_queue_data_maintenance" {
  subscription = google_pubsub_subscription.job-queue-data-maintenance.name
  role         = "roles/pubsub.subscriber"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue-data-maintenance,
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

resource "google_pubsub_subscription_iam_member" "pubsub_viewer_job_queue_ui" {
  subscription = google_pubsub_subscription.job-queue-ui.name
  role         = "roles/pubsub.viewer"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue-ui,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_viewer_job_queue_import_export" {
  subscription = google_pubsub_subscription.job-queue-import-export.name
  role         = "roles/pubsub.viewer"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue-import-export,
  ]
}

resource "google_pubsub_subscription_iam_member" "pubsub_viewer_job_queue_data_maintenance" {
  subscription = google_pubsub_subscription.job-queue-data-maintenance.name
  role         = "roles/pubsub.viewer"
  member       = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
    google_pubsub_subscription.job-queue-data-maintenance,
  ]
}
