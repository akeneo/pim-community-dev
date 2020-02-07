# 20200211

Get the EE tag to deploy :
`git fetch origin &> /dev/null && git tag --list | grep -E '^v?[0-9]+$' | sort -r | head -n 1`

- In main.tf :

change source with 
`git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=TAGtoDEPLOY`
add field 
`pim_version = "TAGtoDEPLOY"`

- Rename pim.yaml in values.yaml
- In values.yaml, add in PIM :
```
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
```

And upgrade the PIM :

`terraform init -upgrade`
`terraform apply`
