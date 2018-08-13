data "terraform_remote_state" "saas-cluster" {
  backend   = "gcs"
  workspace = "${terraform.workspace}"

  config {
    bucket = "akecld-terraform"
    path   = "saas/${var.google_project_name}/infra/${terraform.workspace}.tfstate"
  }
}

provider "google" {
  project = "${var.google_project_name}"
  region  = "${data.terraform_remote_state.saas-cluster.google_project_region}"
  version = "~> 1.8"
}

terraform {
  backend "gcs" {
    bucket = "akecld-terraform"

    #prefix = "saas/${var.google_project_name}/pim-ai"
  }
}

data "template_file" "pimmaster_dns_name" {
  template = "${format("%s.%s",var.subdomain==""? var.pfid:var.subdomain,data.terraform_remote_state.saas-cluster.akeneo_cloud_managed_zone_dns)}"
}

data "template_file" "cluster_name" {
  template = "${coalesce(var.cluster_dns_name,data.terraform_remote_state.saas-cluster.default_cluster)}"
}

data "template_file" "cluster_dns_rrdatas" {
  template = "${format("%s-%s-%s.%s.",data.terraform_remote_state.saas-cluster.env_name, data.template_file.cluster_name.rendered, var.google_project_name , data.terraform_remote_state.saas-cluster.akeneo_cloud_managed_zone_dns)}"
}

resource "google_dns_record_set" "pimmaster_dns_name" {
  name = "${format("%s.",data.template_file.pimmaster_dns_name.rendered)}"
  type = "CNAME"
  ttl  = 300

  managed_zone = "${data.terraform_remote_state.saas-cluster.akeneo_cloud_managed_zone_name}"

  rrdatas = ["${data.template_file.cluster_dns_rrdatas.rendered}"]
  project = "akeneo-cloud"
}

data "template_file" "helm_pim_config" {
  template = "${file("${path.module}/pim-master-values.tpl")}"

  vars {
    pfid         = "${var.pfid}"
    projectId          = "${var.google_project_name}"
    dns_suffix         = "${data.terraform_remote_state.saas-cluster.akeneo_cloud_managed_zone_dns}"
    pimmaster_dns_name = "${data.template_file.pimmaster_dns_name.rendered}"
    mailgun_login      = "${data.template_file.mailgun_login.rendered}"
    mailgun_password   = "${random_string.mailgun_password.result}"
  }
}

resource "local_file" "helm_pim_config" {
  content  = "${data.template_file.helm_pim_config.rendered}"
  filename = "./pim-master-values.yaml"
}
