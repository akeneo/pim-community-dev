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

  labels = {
    pfid = local.pfid
  }
}

resource "google_pubsub_subscription" "job-queue" {
  name                 = "${local.pfid}-job-queue"
  topic                = google_pubsub_topic.job-queue.name
  ack_deadline_seconds = 600

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

resource "google_service_account" "pimpubsub" {
  account_id   = format("gs-%s", substr(md5(var.instance_name), 0, 25))
  display_name = "PubSub access for pim (${var.instance_name})"
}

resource "google_service_account_key" "pimpubsub" {
  service_account_id = google_service_account.pimstorage.id
  public_key_type    = "TYPE_X509_PEM_FILE"

  depends_on = [google_service_account.pimpubsub]
}



resource "google_pubsub_topic_iam_member" "pubsub_publisher_business-event" {
  topic  = google_pubsub_topic.business-event.name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pimpubsub.email}"

  depends_on = [
    google_service_account.pimpubsub,
    google_pubsub_topic.business-event,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_publisher_job_queue" {
  topic  = google_pubsub_topic.job-queue.name
  role   = "roles/pubsub.publisher"
  member = "serviceAccount:${google_service_account.pimpubsub.email}"

  depends_on = [
    google_service_account.pimpubsub,
    google_pubsub_topic.job-queue,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_subscriber_webhook" {
  topic  = google_pubsub_subscription.webhook.name
  role   = "roles/pubsub.subsriber"
  member = "serviceAccount:${google_service_account.pimpubsub.email}"

  depends_on = [
    google_service_account.pimpubsub,
    google_pubsub_subscription.webhook,
  ]
}

resource "google_pubsub_topic_iam_member" "pubsub_subscriber_job_queue" {
  topic  = google_pubsub_subscription.job-queue.name
  role   = "roles/pubsub.subsriber"
  member = "serviceAccount:${google_service_account.pimpubsub.email}"

  depends_on = [
    google_service_account.pimpubsub,
    google_pubsub_subscription.job-queue,
  ]
}
