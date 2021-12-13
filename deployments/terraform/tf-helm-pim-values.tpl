image:
  pim:
    tag: ${pimVersion}

connectors:
  bigcommerce:
    enabled: ${bigcommerce_connector_enabled}
    pubsub:
      topic_name: ${bigcommerce_connector_topic}
      subscription_name: ${bigcommerce_connector_subscription}
    akeneo_connect_bot_password: ${bigcommerce_connector_akeneo_connect_bot_password}
    akeneo_connect_bot_client_secret: ${bigcommerce_connector_akeneo_connect_bot_client_secret}

elasticsearch:
  cluster:
    env:
      cluster.routing.allocation.disk.watermark.low: .97
      cluster.routing.allocation.disk.watermark.high: .98
      cluster.routing.allocation.disk.watermark.flood_stage: .99
  master:
    podAnnotations:
      tags.akeneo.com/pfid: ${pfid}
      # app.kubernetes.io/name: # already set by default by ES chart. Cf  https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L5
      # app.kubernetes.io/component: # already set by default by ES chart. Cf https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L6
      tags.akeneo.com/product_reference: serenity
      # helm.sh/chart: # already set by default by ES chart. Cf https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L6
      tags.akeneo.com/instance_dns_zone: ${dnsZone}
      tags.akeneo.com/instance_dns_record: ${instanceName}
      tags.akeneo.com/papo_project_code: ${papoProjectCode}
  client:
    podAnnotations:
      tags.akeneo.com/pfid: ${pfid}
      # app.kubernetes.io/name: # already set by default by ES chart. Cf  https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L5
      # app.kubernetes.io/component: # already set by default by ES chart. Cf https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L6
      tags.akeneo.com/product_reference: serenity
      # helm.sh/chart: # already set by default by ES chart. Cf https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L6
      tags.akeneo.com/instance_dns_zone: ${dnsZone}
      tags.akeneo.com/instance_dns_record: ${instanceName}
      tags.akeneo.com/papo_project_code:  ${papoProjectCode}
  data:
    podAnnotations:
      tags.akeneo.com/pfid: ${pfid}
      # app.kubernetes.io/name: # already set by default by ES chart. Cf  https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L5
      # app.kubernetes.io/component: # already set by default by ES chart. Cf https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L6
      tags.akeneo.com/product_reference: serenity
      # helm.sh/chart: # already set by default by ES chart. Cf https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L6
      tags.akeneo.com/instance_dns_zone: ${dnsZone}
      tags.akeneo.com/instance_dns_record: ${instanceName}
      tags.akeneo.com/papo_project_code: ${papoProjectCode}

memcached:
  podAnnotations:
    tags.akeneo.com/pfid: ${pfid}
    # app.kubernetes.io/name: # already set by default by Memcached chart. Cf  https://github.com/helm/charts/blob/09324a8a8fdc9b9261ce829486421c345109475b/stable/memcached/templates/_helpers.tpl#L30
    app.kubernetes.io/component: memcached
    tags.akeneo.com/product_reference: serenity
    # helm.sh/chart: # already set by default by Memcached chart. Cf https://github.com/helm/charts/blob/09324a8a8fdc9b9261ce829486421c345109475b/stable/memcached/templates/_helpers.tpl#L31
    tags.akeneo.com/instance_dns_zone: ${dnsZone}
    tags.akeneo.com/instance_dns_record: ${instanceName}
    tags.akeneo.com/papo_project_code: ${papoProjectCode}

global:
  extraLabels:
    type: ${type}
    instanceName: ${instanceName}
    pfid: ${pfid}
    instance_dns_record: ${instanceName}
    instance_dns_zone: ${dnsZone}
    papo_project_code: ${papoProjectCode}
    papo_project_code_truncated: ${papoProjectCodeTruncated}
    papo_project_code_hashed: ${papoProjectCodeHashed}
    product_reference_code: ${product_reference_code}
    product_reference_type: ${product_reference_type}

common:
  gcpProjectID: ${projectId}
  googleZone: ${googleZone}
  pimMasterDomain: ${pimmaster_dns_name}

backup:
  projectId: ${projectId}
  zone: ${googleZone}

mailer:
  login: ${mailgun_login_email}
  password: ${mailgun_password}

pim:
  serviceAccountKey: ${pimStoragekey}
  pubsub:
    subscription_webhook: ${subscription_webhook}
    subscription_job_queue_ui: ${subscription_job_queue_ui}
    subscription_job_queue_import_export: ${subscription_job_queue_import_export}
    subscription_job_queue_data_maintenance: ${subscription_job_queue_data_maintenance}
    topic_business_event: ${topic_business_event}
    topic_job_queue_ui: ${topic_job_queue_ui}
    topic_job_queue_import_export: ${topic_job_queue_import_export}
    topic_job_queue_data_maintenance: ${topic_job_queue_data_maintenance}
  storage:
    bucketName: ${bucketName}
  monitoring:
    authenticationToken: ${monitoring_authentication_token}

mysql:
  mysql:
    dataDiskSize: ${mysql_disk_size}Gi
  common:
    persistentDisks:
    - ${mysql_disk_name}
    class: ${mysql_disk_storage_class}


%{ if type == "tria" }
free_trial:
  enabled: true
  akeneo_connect_saml_entity_id: ${akeneo_connect_saml_entity_id}
  akeneo_connect_saml_certificate: ${akeneo_connect_saml_certificate}
  akeneo_connect_saml_sp_certificate_base64: ${akeneo_connect_saml_sp_certificate_base64}
  akeneo_connect_saml_sp_private_key_base64: ${akeneo_connect_saml_sp_private_key_base64}
  akeneo_connect_api_client_secret: ${akeneo_connect_api_client_secret}
  akeneo_connect_api_client_password: ${akeneo_connect_api_client_password}
  ft_catalog_api_client_id: ${ft_catalog_api_client_id}
  ft_catalog_api_password: ${ft_catalog_api_password}
  ft_catalog_api_secret: ${ft_catalog_api_secret}
%{ endif }
