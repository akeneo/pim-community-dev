image:
  pim:
    tag: ${pim.version}

connectors:
  bigcommerce:
    enabled: ${bigcommerce_connector.enabled}
    pubsub:
      topic_name: ${bigcommerce_connector.topic}
      subscription_name: ${bigcommerce_connector.subscription}
    akeneo_connect_bot_password: ${bigcommerce_connector.akeneo_connect.bot_password}
    akeneo_connect_bot_client_secret: ${bigcommerce_connector.akeneo_connect.bot_client_secret}

elasticsearch:
  cluster:
    env:
      cluster.routing.allocation.disk.watermark.low: .97
      cluster.routing.allocation.disk.watermark.high: .98
      cluster.routing.allocation.disk.watermark.flood_stage: .99
  master:
    podAnnotations:
      tags.akeneo.com/pfid: ${pim.pfid}
      tags.akeneo.com/product_reference: serenity
      tags.akeneo.com/instance_dns_zone: ${pim.dns_zone}
      tags.akeneo.com/instance_dns_record: ${pim.dns_record}
      tags.akeneo.com/papo_project_code: ${portal.project_code}
    heapSize: ${elasticsearch.master.heap_size}
    ${indent(4,replace(yamlencode({resources: "${elasticsearch.master.resources}"}),"\"",""))}
  client:
    podAnnotations:
      tags.akeneo.com/pfid: ${pim.pfid}
      tags.akeneo.com/product_reference: serenity
      tags.akeneo.com/instance_dns_zone: ${pim.dns_zone}
      tags.akeneo.com/instance_dns_record: ${pim.dns_record}
      tags.akeneo.com/papo_project_code: ${portal.project_code}
    heapSize: ${elasticsearch.client.heap_size}
    ${indent(4,replace(yamlencode({resources: "${elasticsearch.client.resources}"}),"\"",""))}
  data:
    podAnnotations:
      tags.akeneo.com/pfid: ${pim.pfid}
      tags.akeneo.com/product_reference: serenity
      tags.akeneo.com/instance_dns_zone: ${pim.dns_zone}
      tags.akeneo.com/instance_dns_record: ${pim.dns_record}
      tags.akeneo.com/papo_project_code: ${portal.project_code}
    heapSize: ${elasticsearch.data.heap_size}
    ${indent(4,replace(yamlencode({resources: "${elasticsearch.data.resources}"}),"\"",""))}
memcached:
  podAnnotations:
    tags.akeneo.com/pfid: ${pim.pfid}
    app.kubernetes.io/component: memcached
    tags.akeneo.com/product_reference: serenity
    tags.akeneo.com/instance_dns_zone: ${pim.dns_zone}
    tags.akeneo.com/instance_dns_record: ${pim.dns_record}
    tags.akeneo.com/papo_project_code: ${portal.project_code}
  ${indent(2,replace(yamlencode({resources: "${memcached.resources}"}),"\"",""))}
global:
  extraLabels:
    type: ${pim.type}
    instanceName: ${pim.dns_record}
    pfid: ${pim.pfid}
    instance_dns_record: ${pim.dns_record}
    instance_dns_zone: ${pim.dns_zone}
    papo_project_code: ${portal.project_code}
    papo_project_code_truncated: ${portal.project_code_truncated}
    papo_project_code_hashed: ${portal.project_code_hashed}

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
  base_mailer_url: "smtp://${mailgun.host}:${mailgun.port}"

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

mysql:
  mysql:
    dataDiskSize: ${mysql.disk_size}Gi
    innodbBufferPoolSize: ${mysql.innodb_buffer_pool_size}
    ${indent(4,replace(yamlencode({resources: "${mysql.resources}"}),"\"",""))}
  common:
    persistentDisks:
    - ${mysql.disk_name}
    class: ${mysql.disk_storage_class}
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
%{ endif }
