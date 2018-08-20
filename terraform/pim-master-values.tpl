global:
  extraLabels:
    pfid: ${pfid}
common:
  pimMasterUrl: ${pimmaster_dns_name}
  mailgunLogin: ${mailgun_login}
  mailgunPassword: ${mailgun_password}

nfs:
  storageClass:
    name: nfs-${pfid}
