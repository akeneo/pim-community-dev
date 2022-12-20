resource "google_workflows_workflow" "migration_legacy_ucs" {
  name          = "migration-legacy-ucs"
  project       = var.project_id
  region        = var.region
  description   = "Workflow to migrate tenant environments from legacy to UCS"
  service_account = "ucs-migration-uat-wip@akecld-prd-pim-saas-demo.iam.gserviceaccount.com"
  source_contents = join("", [
    templatefile(
      "${path.module}/migration-legacy-ucs/workflow.yaml",{}
    ),
    templatefile(
      "${path.module}/migration-legacy-ucs/cloudbuild/initialArgocdAppCreation.yaml",{}
    ),
    templatefile(
      "${path.module}/migration-legacy-ucs/cloudbuild/esSnapshotCreation.yaml",{}
    ),
    templatefile(
      "${path.module}/migration-legacy-ucs/cloudbuild/scaleDownFrontsAndCronJobs.yaml",{}
    ),
    templatefile(
      "${path.module}/migration-legacy-ucs/cloudbuild/scaleDownDaemon.yaml",{}
    ),
    templatefile(
      "${path.module}/migration-legacy-ucs/cloudbuild/scaleDownMysql.yaml",{}
    ),
    templatefile(
      "${path.module}/migration-legacy-ucs/cloudbuild/updateArgoCdApp.yaml",{}
    )
    ])
}
