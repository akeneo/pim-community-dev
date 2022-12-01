editionFlag:
  enabled: ${pim.use_edition_flag}

image:
  pim:
    tag: ${pim.version}

global:
  extraLabels: &global_extraLabels
    tags.akeneo.com/instance_fqdn: ${pim.fqdn}
    tags.akeneo.com/instance_type: ${pim.type}
    tags.akeneo.com/instance_dns_record: ${pim.dns_record}
    tags.akeneo.com/instance_dns_zone: ${pim.dns_zone}
    tags.akeneo.com/portal_project_code: ${portal.project_code}
    tags.akeneo.com/product_reference_code: ${pim.product_reference_code}
    tags.akeneo.com/product_reference_type: ${pim.product_reference_type}
    tags.akeneo.com/product_reference_size: ${pim.product_reference_size}
    tags.akeneo.com/pfid: ${pim.pfid} # deprecated
    tags.akeneo.com/product_reference: serenity # deprecated
    tags.akeneo.com/papo_project_code: ${portal.project_code} # deprecated
    type: ${pim.type} # deprecated
    instanceName: ${pim.dns_record} # deprecated
    pfid: ${pim.pfid} # deprecated
    instance_dns_record: ${pim.dns_record} # deprecated
    instance_dns_zone: ${pim.dns_zone} # deprecated
    papo_project_code: ${portal.project_code} # deprecated
    papo_project_code_truncated: ${portal.project_code_truncated} # deprecated
    papo_project_code_hashed: ${portal.project_code_hashed} # deprecated

elasticsearch:
  cluster:
    env:
      cluster.routing.allocation.disk.watermark.low: .97
      cluster.routing.allocation.disk.watermark.high: .98
      cluster.routing.allocation.disk.watermark.flood_stage: .99
%{ if pim.type == "tria" }
    plugins: null
  keystore: null
%{ endif }
  master:
    podAnnotations:
      app.kubernetes.io/name: elasticsearch
      app.kubernetes.io/component: master
      <<: *global_extraLabels
    heapSize: ${elasticsearch.master.heap_size}
    ${indent(4,replace(yamlencode({resources: "${elasticsearch.master.resources}"}),"\"",""))}
  client:
    podAnnotations:
      app.kubernetes.io/name: elasticsearch
      app.kubernetes.io/component: client
      <<: *global_extraLabels
    heapSize: ${elasticsearch.client.heap_size}
    ${indent(4,replace(yamlencode({resources: "${elasticsearch.client.resources}"}),"\"",""))}
  data:
    podAnnotations:
      app.kubernetes.io/name: elasticsearch
      app.kubernetes.io/component: data
      <<: *global_extraLabels
    heapSize: ${elasticsearch.data.heap_size}
    ${indent(4,replace(yamlencode({resources: "${elasticsearch.data.resources}"}),"\"",""))}

common:
  gcpProjectID: ${google_cloud_project.id}
  googleZone: ${google_cloud_project.zone}
  pimMasterDomain: ${pim.master_dns_name}

backup:
  projectId: ${google_cloud_project.id}
  zone: ${google_cloud_project.zone}

mailer:
  login: ${mailgun.login_email}
  password: ${mailgun.password}
  base_mailer_dsn: "smtp://${mailgun.login_email}:${mailgun.password}@${mailgun.host}:${mailgun.port}"

pim:
  serviceAccountKey: ${pim.storage_key}
  pubsub:
    subscription_webhook: ${pim.subscription.webhook}
    subscription_job_queue_ui: ${pim.subscription.job_queue_ui}
    subscription_job_queue_import_export: ${pim.subscription.job_queue_import_export}
    subscription_job_queue_data_maintenance: ${pim.subscription.job_queue_data_maintenance}
    topic_business_event: ${pim.topic.business_event}
    topic_job_queue_ui: ${pim.topic.job_queue_ui}
    topic_job_queue_import_export: ${pim.topic.job_queue_import_export}
    topic_job_queue_data_maintenance: ${pim.topic.job_queue_data_maintenance}
  storage:
    bucketName: ${pim.bucket_name}
  monitoring:
    authenticationToken: ${pim.monitoring_authentication_token}
  daemons:
    job-consumer-process:
      replicas: ${pim.daemons.job-consumer-process.replicas}
    webhook-consumer-process:
      replicas: ${pim.daemons.webhook-consumer-process.replicas}
  web:
    replicas: ${pim.web.replicas}
  api:
    replicas: ${pim.api.replicas}
%{ if pim.type == "tria" }
# Disable admin for free_trial SSO
  hook:
    addAdmin:
      enabled: false
%{ if pim.use_edition_flag }
# Set Catalog for free_trial
  defaultCatalog: tria/src/Akeneo/FreeTrial/back/Infrastructure/Symfony/Resources/fixtures/free_trial_catalog
%{ endif }
%{ endif }

mysql:
  mysql:
    dataDiskSize: ${mysql.disk_size}Gi
    innodbBufferPoolSize: ${mysql.innodb_buffer_pool_size}
    ${indent(4,replace(yamlencode({resources: "${mysql.resources}"}),"\"",""))}
  common:
    persistentDisks:
    - ${mysql.disk_name}
    class: ${mysql.disk_storage_class}
  extraAnnotations:
    app.kubernetes.io/name: mysql
    app.kubernetes.io/component: database

%{ if pim.type == "tria" }
free_trial:
  enabled: true
  akeneo_connect_saml_entity_id: ${ft_catalog.akeneo_connect.saml.entity_id}
  akeneo_connect_saml_certificate: ${ft_catalog.akeneo_connect.saml.certificate}
  akeneo_connect_saml_sp_certificate_base64: ${ft_catalog.akeneo_connect.saml.sp_certificate_base64}
  akeneo_connect_saml_sp_private_key_base64: ${ft_catalog.akeneo_connect.saml.sp_private_key_base64}
  akeneo_connect_api_client_secret: ${ft_catalog.akeneo_connect.api.client_secret}
  akeneo_connect_api_client_password: ${ft_catalog.akeneo_connect.api.client_password}
  ft_catalog_api_client_id: ${ft_catalog.api_client_id}
  ft_catalog_api_password: ${ft_catalog.api_password}
  ft_catalog_api_secret: ${ft_catalog.api_secret}
###
%{ endif }

performance_analytics:
  service_account_key: ${performance_analytics.service_account_key}
  pubsub:
    topic_performance_analytics: ${performance_analytics.topic.name}
