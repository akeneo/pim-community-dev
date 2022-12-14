module "features-ci" {
  source       = "../pim-saas-env/cloudbuild"
  project_id   = "akecld-prd-pim-saas-fci"
  project_name = "pim-saas-fci"
  env          = "fci"
  env_shorted  = "fci"
  tf_bucket    = "akecld-terraform-pim-saas-fci"
  clusters     = {
    cluster1 : {
      region                = "europe-west1"
      region_shorted        = "euw1"
      cluster_number_region = "1"
    }
  }
}
