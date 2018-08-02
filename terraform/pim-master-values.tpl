global:
  extraLabels:
    pfid: ${pfid-srnt}
common:
  pimMasterUrl: ${pimmaster_dns_name}
  mailgunLogin: ${mailgun_login}
  mailgunPassword: ${mailgun_password}


image:
  pim:
    tag: cloud2.3-AOB-v1.0.0-BETA2

nfs:
  persitence:
    storageClass: standard
  storageClass:
    name: nfs-${pfid-srnt}
