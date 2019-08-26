global:
  extraLabels:
    instanceName: ${instanceName}
    pfid: ${pfid}

common:
  gcpProjectID: ${projectId}
  googleZone: ${googleZone}
  pimMasterDomain: ${pimmaster_dns_name}
  containerName: ${container_name}

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
