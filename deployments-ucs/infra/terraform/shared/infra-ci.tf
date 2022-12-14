module "infra-ci" {
  source       = "../pim-saas-env/cloudbuild"
  project_id   = "akecld-prd-pim-saas-inci"
  project_name = "pim-saas-inci"
  env          = "inci"
  env_shorted  = "inci"
  tf_bucket    = "akecld-terraform-pim-saas-inci"
  clusters     = {
    cluster1 : {
      region                = "europe-west1"
      region_shorted        = "euw1"
      cluster_number_region = "1"
    }
  }
}
