image:
  pim:
    repository: eu.gcr.io/akeneo-ci/pim-enterprise-dev
    pullPolicy: Always
    tag: ${pimVersion}

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
