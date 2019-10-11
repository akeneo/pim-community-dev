global:
  extraLabels:
    instanceName: ${instanceName}
    pfid: ${pfid}
    instance_dns_record: ${instanceName}
    instance_dns_zone: ${dnsZone}
    papo_project_code: ${papoProjectCode}   

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
  host: ${mailgun_host}
  port: ${mailgun_port}
pim:
  storage:
    bucketName: ${bucketName}
    serviceAccountKey: ${pimStoragekey}
