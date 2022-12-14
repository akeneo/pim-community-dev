module "features-sandbox" {
  source       = "../pim-saas-env/cloudbuild"
  project_id   = "akecld-prd-pim-saas-fsan"
  project_name = "pim-saas-fsan"
  env          = "fsan"
  env_shorted  = "fsan"
  tf_bucket    = "akecld-terraform-pim-saas-fsan"
  clusters     = {
    cluster1 : {
      region                = "europe-west1"
      region_shorted        = "euw1"
      cluster_number_region = "1"
    }
  }
}
