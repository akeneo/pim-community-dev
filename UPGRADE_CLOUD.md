# 4.0

- In main.tf :
change source with "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=master"
add field pim_version = "master"

Rename pim.yaml in values.yaml
- In values.yaml, add in PIM :

  hook:
    addAdmin:
      enabled: false
    installPim:
      enabled: false
    upgradePim:
      enabled: true
      hook: post-upgrade
    upgradeES:
      enabled: true


terraform init -upgrade
terraform apply
