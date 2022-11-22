module "ci-demo" {
  source     = "../pim-saas-env/cloudbuild"
  project_id = "akecld-prd-pim-saas-demo"
  env        = "demo"
  tf_bucket  = "akecld-terraform-pim-saas-demo"
  regions = [
    "europe-west1",
    "europe-west3",
    "us-central1",
    "australia-southeast1"
  ]
}
