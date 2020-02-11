# 20200211

Downscale "pim-web & pim-daemons" containers to 0, and remove cronjob :

`kubectl delete -n srnt-${INSTANCE_NAME} cronjob --all`
`kubectl scale -n srnt-${INSTANCE_NAME} deploy/pim-web deploy/pim-daemon --replicas=0`

Get the EE tag to deploy :

`git clone git@github.com:akeneo/pim-enterprise-dev.git && cd pim-enterprise-dev && git fetch origin &> /dev/null && git tag --list | grep -E '^v?[0-9]+$' | sort -r | head -n 1`

- In main.tf :

Change source with 

`git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=TAGtoDEPLOY`

Add field in module "PIM"

`pim_version = "TAGtoDEPLOY"`

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

Upscale pim-web & pim-daemon :

`kubectl scale -n srnt-${INSTANCE_NAME} deploy/pim-web deploy/pim-daemon --replicas=2`

In values.yaml, remove "hook"


-- To test migrations : SELECT COUNT(*) FROM akeneo_asset_manager_asset;
