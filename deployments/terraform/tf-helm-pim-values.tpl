image:
  pim:
    tag: ${pimVersion}

elasticsearch:
  master:
    podAnnotations:
      tags.akeneo.com/pfid: ${pfid}
      # app.kubernetes.io/name: # already set by default by ES chart. Cf  https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L5
      # app.kubernetes.io/component: # already set by default by ES chart. Cf https://github.com/helm/charts/blob/master/stable/elasticsearch/templates/master-statefulset.yaml#L6
      tags.akeneo.com/product_version: ${pimVersion}
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
      tags.akeneo.com/product_version: ${pimVersion}
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
      tags.akeneo.com/product_version: ${pimVersion}
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
  storage:
    bucketName: ${bucketName}
    serviceAccountKey: ${pimStoragekey}
  monitoring:
    authenticationToken: ${monitoring_authentication_token}
