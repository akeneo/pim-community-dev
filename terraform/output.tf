output "cluster_name" {
  value = "${data.template_file.cluster_name.rendered}"
}

output "env_name" {
  value = "${data.terraform_remote_state.saas-cluster.env_name}"
}