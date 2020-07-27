//
// TOPICS
//

resource "google_pubsub_topic" "business_event" {
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
