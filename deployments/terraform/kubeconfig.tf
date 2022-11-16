data "google_container_cluster" "main" {
  name     = var.google_project_zone
  location = var.google_project_zone
  project  = var.google_project_id
}

# From https://github.com/terraform-providers/terraform-provider-kubernetes/blob/master/kubernetes/test-infra/gke/main.tf

resource "local_file" "kubeconfig" {
  filename        = ".kubeconfig"
  file_permission = "0600"
  content         = <<EOF
apiVersion: v1
kind: Config
current-context: ${data.google_container_cluster.main.name}
contexts: [{name: ${data.google_container_cluster.main.name}, context: {cluster: ${data.google_container_cluster.main.name}, user: user}}]
users:
- name: user
  user:
    exec:
      apiVersion: client.authentication.k8s.io/v1beta1
      args: null
      command: gke-gcloud-auth-plugin
      env: null
      installHint: Install gke-gcloud-auth-plugin for use with kubectl by following
        https://cloud.google.com/blog/products/containers-kubernetes/kubectl-auth-changes-in-gke
      interactiveMode: Never
      provideClusterInfo: true
clusters:
- name: ${data.google_container_cluster.main.name}
  cluster:
    server: https://${data.google_container_cluster.main.endpoint}
    certificate-authority-data: ${data.google_container_cluster.main.master_auth[0].cluster_ca_certificate}
EOF

}

