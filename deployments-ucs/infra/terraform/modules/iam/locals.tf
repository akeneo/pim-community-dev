locals {
  ci_sa = "serviceAccount:${var.ci_service_account}@${var.project_id}.iam.gserviceaccount.com"
}
