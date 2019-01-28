global:
  extraLabels:
    pfid: ${pfid}
common:
  gcpProjectID: ${projectId}
  googleZone: ${googleZone}
  pimMasterDomain: ${pimmaster_dns_name}

nfs:
  storageClass:
    name: nfs-${pfid}

mailer:
  login: ${mailgun_login_email}
  password: ${mailgun_password}
  host: ${mailgun_host}
  port: ${mailgun_port}
  from_address: no-reply@akeneo.com