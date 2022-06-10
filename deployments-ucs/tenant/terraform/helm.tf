resource "random_string" "monitoring_authentication_token" {
  length  = 32
  special = false
}

resource "random_string" "mysql_user_password" {
  length  = 16
  special = false
}

resource "random_string" "mysql_root_password" {
  length  = 16
  special = false
}

locals {
  helm-default-values = yamldecode(file("${path.module}/../values.yaml"))
  tshirt-size-index-mapping = {
    S  = 0
    M  = 1
    L  = 2
    XL = 3
  }
  tshirt-size-index = can(regex("^[1-9][0-9]*XL$", var.product_reference_size)) ? local.tshirt-size-index-mapping["XL"] + trimsuffix(var.product_reference_size, "XL") - 1 : local.tshirt-size-index-mapping[var.product_reference_size]
  mysql-memory-unit = "M"
  jvm-heap-unit     = "m"
  k8s-memory-unit   = "Mi"
  k8s-cpu-unit      = "m"
  fqdn              = trimsuffix(var.dns_external, ".")
}

resource "local_file" "helm_pim_config" {
  content = templatefile("${path.module}/tf-helm-pim-values.tpl",
    {
      google_cloud_project = {
        id   = var.google_project_id
        zone = var.google_project_zone
      }

      mailgun = {
        login_email = local.mailgun_login_email
        password    = random_string.mailgun_password.result
        host        = var.mailgun_host
        port        = var.mailgun_port
      }

      pim = {
        type                            = local.type
        use_edition_flag                = var.use_edition_flag
        product_reference_code          = var.product_reference_code
        product_reference_type          = var.product_reference_type
        product_reference_size          = var.product_reference_size
        pfid                            = local.pfid
        version                         = var.pim_version
        fqdn                            = local.fqdn
        instanceName                    = var.instance_name
        dns_record                      = var.instance_name
        dns_zone                        = var.dns_zone
        master_dns_name                 = local.fqdn
        storage_key                     = google_service_account_key.pim_service_account.private_key
        bucket_name                     = google_storage_bucket.srnt_bucket.name
        monitoring_authentication_token = local.monitoring_authentication_token

        api = {
          replicas = local.helm-default-values.pim.api.replicas + local.tshirt-size-index
        }

        web = {
          replicas = local.helm-default-values.pim.web.replicas + local.tshirt-size-index
        }

        daemons = {
          job-consumer-process = {
            replicas = local.helm-default-values.pim.daemons.job-consumer-process.replicas + local.tshirt-size-index
          }
          webhook-consumer-process = {
            replicas = local.helm-default-values.pim.daemons.webhook-consumer-process.replicas + local.tshirt-size-index
          }
        }
      }

      portal = {
        project_code = var.papo_project_code
        project_code_truncated = substr(
          var.papo_project_code,
          0,
          min(63, length(var.papo_project_code)),
        )
        project_code_hashed = md5(var.papo_project_code)
      }

      elasticsearch = {
        client = {
          heap_size = format(
            "%d%s", ceil(
              trimsuffix(local.helm-default-values.elasticsearch.client.heapSize, local.jvm-heap-unit) +
              trimsuffix(local.helm-default-values.elasticsearch.client.heapSize, local.jvm-heap-unit) / 2 * local.tshirt-size-index
            ),
            local.jvm-heap-unit
          )
          resources = {
            limits = {
              memory = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.client.resources.limits.memory, local.k8s-memory-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.client.resources.limits.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-memory-unit
              )
            }
            requests = {
              cpu = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.client.resources.requests.cpu, local.k8s-cpu-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.client.resources.requests.cpu, local.k8s-cpu-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-cpu-unit
              )
              memory = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.client.resources.requests.memory, local.k8s-memory-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.client.resources.requests.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-memory-unit
              )
            }
          }
        }
        master = {
          heap_size = format(
            "%d%s", ceil(
              trimsuffix(local.helm-default-values.elasticsearch.master.heapSize, local.jvm-heap-unit) +
              trimsuffix(local.helm-default-values.elasticsearch.master.heapSize, local.jvm-heap-unit) / 2 * local.tshirt-size-index
            ),
            local.jvm-heap-unit
          )
          resources = {
            limits = {
              memory = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.master.resources.limits.memory, local.k8s-memory-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.master.resources.limits.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-memory-unit
              )
            }
            requests = {
              cpu = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.master.resources.requests.cpu, local.k8s-cpu-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.master.resources.requests.cpu, local.k8s-cpu-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-cpu-unit
              )
              memory = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.master.resources.requests.memory, local.k8s-memory-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.master.resources.requests.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-memory-unit
              )
            }
          }
        }
        data = {
          heap_size = format(
            "%d%s", ceil(
              trimsuffix(local.helm-default-values.elasticsearch.data.heapSize, local.jvm-heap-unit) +
              trimsuffix(local.helm-default-values.elasticsearch.data.heapSize, local.jvm-heap-unit) / 2 * local.tshirt-size-index
            ),
            local.jvm-heap-unit
          )
          resources = {
            limits = {
              memory = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.data.resources.limits.memory, local.k8s-memory-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.data.resources.limits.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-memory-unit
              )
            }
            requests = {
              cpu = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.data.resources.requests.cpu, local.k8s-cpu-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.data.resources.requests.cpu, local.k8s-cpu-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-cpu-unit
              )
              memory = format(
                "%d%s", ceil(
                  trimsuffix(local.helm-default-values.elasticsearch.data.resources.requests.memory, local.k8s-memory-unit) +
                  trimsuffix(local.helm-default-values.elasticsearch.data.resources.requests.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
                ),
                local.k8s-memory-unit
              )
            }
          }
        }
      }

      memcached = {
        resources = {
          limits = {
            memory = format(
              "%d%s", ceil(
                trimsuffix(local.helm-default-values.memcached.resources.limits.memory, local.k8s-memory-unit) +
                trimsuffix(local.helm-default-values.memcached.resources.limits.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
              ),
              local.k8s-memory-unit
            )
          }
          requests = {
            cpu = format(
              "%d%s", ceil(
                trimsuffix(local.helm-default-values.memcached.resources.requests.cpu, local.k8s-cpu-unit) +
                trimsuffix(local.helm-default-values.memcached.resources.requests.cpu, local.k8s-cpu-unit) / 2 * local.tshirt-size-index
              ),
              local.k8s-cpu-unit
            )
            memory = format(
              "%d%s", ceil(
                trimsuffix(local.helm-default-values.memcached.resources.requests.memory, local.k8s-memory-unit) +
                trimsuffix(local.helm-default-values.memcached.resources.requests.memory, local.k8s-memory-unit) / 2 * local.tshirt-size-index
              ),
              local.k8s-memory-unit
            )
          }
        }
      }

      mysql = {
        userPassword       = random_string.mysql_user_password.result
        rootPassword       = random_string.mysql_root_password.result
        disk_name          = google_compute_disk.mysql-disk.id
        disk_size          = google_compute_disk.mysql-disk.size
        disk_storage_class = google_compute_disk.mysql-disk.type == "pd-ssd" ? "ssd-retain-csi" : "standard-retain-csi"
        innodb_buffer_pool_size = format(
          "%d%s", ceil(
            trimsuffix(local.helm-default-values.mysql.mysql.innodbBufferPoolSize, local.mysql-memory-unit) +
            1024 * local.tshirt-size-index
          ),
          local.mysql-memory-unit
        )
        resources = {
          limits = {
            memory = format(
              "%d%s", ceil(
                trimsuffix(local.helm-default-values.mysql.mysql.resources.limits.memory, local.k8s-memory-unit) +
                1024 * local.tshirt-size-index * 130 / 100
              ),
              local.k8s-memory-unit
            )
          }
          requests = {
            cpu = format(
              "%d%s", ceil(
                trimsuffix(local.helm-default-values.mysql.mysql.resources.requests.cpu, local.k8s-cpu-unit) +
                trimsuffix(local.helm-default-values.mysql.mysql.resources.requests.cpu, local.k8s-cpu-unit) / 2 * local.tshirt-size-index
              ),
              local.k8s-cpu-unit
            )
            memory = format(
              "%d%s", ceil(
                trimsuffix(local.helm-default-values.mysql.mysql.resources.limits.memory, local.k8s-memory-unit) +
                1024 * local.tshirt-size-index * 130 / 100
              ),
              local.k8s-memory-unit
            )
          }
        }
      }

      ft_catalog = {
        api_client_id = var.ft_catalog_api_client_id
        api_password  = var.ft_catalog_api_password
        api_secret    = var.ft_catalog_api_secret
        akeneo_connect = {
          saml = {
            entity_id             = var.akeneo_connect_saml_entity_id
            certificate           = var.akeneo_connect_saml_certificate
            sp_certificate_base64 = var.akeneo_connect_saml_sp_certificate_base64
            sp_private_key_base64 = var.akeneo_connect_saml_sp_private_key_base64
          }
          api = {
            client_secret   = var.akeneo_connect_api_client_secret
            client_password = var.akeneo_connect_api_client_password
          }
        }
      }
    }
  )
  filename = "./tf-helm-pim-values.yaml"
}

resource "null_resource" "helm_release_pim" {
  triggers = {
    tf-helm-pim-values-yaml = sha256(local_file.helm_pim_config.content)
    values-override         = fileexists(trimspace(var.chart_values_override_path)) ? file(var.chart_values_override_path) : ""
  }

  depends_on = [
    local_file.kubeconfig,
    google_storage_bucket_iam_member.srnt_bucket_pimstorage,
  ]

  provisioner "local-exec" {
    interpreter = ["/usr/bin/env", "bash", "-c"]

    command = <<EOF
echo "You can now launch helm install/upgrade"
EOF
  }
}
