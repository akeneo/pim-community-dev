global:
  extraLabels:
    pfid: ${pfid}
common:
  gcpProjectID: ${projectId}
  googleZone: ${googleZone}
  pimMasterDomain: ${pimmaster_dns_name}

pimUsers:
  cloudadmin:
    password: ${pim_cloud_admin_password}

mysql:
  mysql:
    userPassword: ${mysql_root_password}
    rootPassword: ${mysql_akeneo_pim_password}

nfs:
  storageClass:
    name: nfs-${pfid}

mailer:
  login: ${mailgun_login_email}
  password: ${mailgun_password}
  host: ${mailgun_host}
  port: ${mailgun_port}
