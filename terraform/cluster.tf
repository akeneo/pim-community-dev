data "google_client_config" "default" {}

data "google_container_cluster" "main-cluster" {
  name = "${coalesce(var.cluster_dns_name,data.terraform_remote_state.saas-cluster.default_cluster)}"
  zone = "${lookup(data.terraform_remote_state.saas-cluster.clusters[coalesce(var.cluster_dns_name,data.terraform_remote_state.saas-cluster.default_cluster)], "zone")}"
}

resource "local_file" "kubeconfig" {
  content = <<KUBECONFIG
apiVersion: v1
clusters:
- cluster:
    certificate-authority-data: ${data.google_container_cluster.main-cluster.master_auth.0.cluster_ca_certificate}
    server: https://${data.google_container_cluster.main-cluster.endpoint}
  name: gke_${var.google_project_name}_${data.google_container_cluster.main-cluster.zone}_${data.google_container_cluster.main-cluster.name}
contexts:
- context:
    cluster: gke_${var.google_project_name}_${data.google_container_cluster.main-cluster.zone}_${data.google_container_cluster.main-cluster.name}
    user: gke_${var.google_project_name}_${data.google_container_cluster.main-cluster.zone}_${data.google_container_cluster.main-cluster.name}
  name: gke_${var.google_project_name}_${data.google_container_cluster.main-cluster.zone}_${data.google_container_cluster.main-cluster.name}
current-context: gke_${var.google_project_name}_${data.google_container_cluster.main-cluster.zone}_${data.google_container_cluster.main-cluster.name}
kind: Config
preferences: {}
users:
- name: gke_${var.google_project_name}_${data.google_container_cluster.main-cluster.zone}_${data.google_container_cluster.main-cluster.name}
  user:
    auth-provider:
      config:
        access-token: ${data.google_client_config.default.access_token}
        expiry-key: '{.credential.token_expiry}'
        token-key: '{.credential.access_token}'
      name: gcp
KUBECONFIG

  filename = "./kubeconfig"
}
