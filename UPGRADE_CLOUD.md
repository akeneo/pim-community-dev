# 20200211

Downscale "pim-web & pim-daemons" containers to 0, and remove cronjob :

`kubectl delete -n srnt-${INSTANCE_NAME} cronjob --all`
`kubectl scale -n srnt-${INSTANCE_NAME} deploy/pim-web deploy/pim-daemon --replicas=0`

- In main.tf :

Change source with 

`git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=v20200211172331`

Add field in module "PIM"

`pim_version = "v20200211172331"`

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

In values.yaml, remove the "hook" part

