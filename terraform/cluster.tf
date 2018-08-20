data "google_client_config" "default" {}

# data "google_container_cluster" "main-cluster" {
#   name = "${coalesce(var.cluster_dns_name,var.cluster_name)}"
#   zone = "${lookup(data.terraform_remote_state.saas-cluster.clusters[coalesce(var.cluster_dns_name,var.cluster_name)], "zone")}"
# }
locals {
  regexp="/w-/w-/w"
}
resource "null_resource" "kubeconfig" {

provisioner "local-exec" {
  command = <<EOF
  gcloud container --project=${var.google_project_name} clusters get-credentials ${var.cluster_name} --zone=${var.cluster_name}
EOF

    environment {
      KUBECONFIG = "./kubeconfig"
}
}

}
