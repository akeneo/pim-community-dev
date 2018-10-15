global:
  extraLabels:
    pfid: ${pfid}
common:
  gcpProjectID: ${projectId}
  googleZone: ${googleZone}
  pimMasterDomain: ${pimmaster_dns_name}

nfs:
  persitence:
    storageClass: standard
  storageClass:
    name: nfs-srnt-${pfid}

mailer:
  login: ${mailgun_login}
  password: ${mailgun_password}
  host: ${mailgun_host}
  port: ${mailgun_port}
  from_address: no-reply@akeneo.com