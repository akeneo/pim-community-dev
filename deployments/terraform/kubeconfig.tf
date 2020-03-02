data "google_container_cluster" "main" {
  name     = "${var.google_project_zone}"
  location = "${var.google_project_zone}"
  project  = "${var.google_project_id}"
}

# From https://github.com/terraform-providers/terraform-provider-kubernetes/blob/master/kubernetes/test-infra/gke/main.tf

resource "local_file" "kubeconfig" {
  filename = ".kubeconfig"

  content = <<EOF
apiVersion: v1
clusters:
- cluster:
    certificate-authority-data: ${data.google_container_cluster.main.master_auth.0.cluster_ca_certificate}
    server: https://${data.google_container_cluster.main.endpoint}
  name: ${data.google_container_cluster.main.name}
contexts:
- context:
    cluster: ${data.google_container_cluster.main.name}
    user: ${data.google_container_cluster.main.master_auth.0.username}
  name: ${data.google_container_cluster.main.name}
current-context: ${data.google_container_cluster.main.name}
kind: Config
preferences: {}
users:
- name: ${data.google_container_cluster.main.master_auth.0.username}
  user:
    client-certificate-data: ${data.google_container_cluster.main.master_auth.0.client_certificate}
    client-key-data: ${data.google_container_cluster.main.master_auth.0.client_key}
    password: ${data.google_container_cluster.main.master_auth.0.password}
    username: ${data.google_container_cluster.main.master_auth.0.username}
EOF
}
