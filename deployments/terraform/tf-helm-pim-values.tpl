image:
  pim:
    tag: ${pimVersion}

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

global:
  extraLabels:
    instanceName: ${instanceName}
    pfid: ${pfid}
    instance_dns_record: ${instanceName}
    instance_dns_zone: ${dnsZone}
    papo_project_code: ${papoProjectCode}
    papo_project_code_truncated: ${papoProjectCodeTruncated}
    papo_project_code_hashed: ${papoProjectCodeHashed}

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
    subscription_job_queue: ${subscription_job_queue}
    topic_business_event: ${topic_business_event}
    topic_job_queue: ${topic_job_queue}
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

