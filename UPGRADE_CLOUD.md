# Upgrade fresh serenity instances

In case of upgrade only :
```
terraform init -upgrade
terraform state mv module.pim.google_monitoring_alert_policy.alert_policy module.pim-monitoring.google_monitoring_alert_policy.alert_policy
terraform state mv module.pim.google_monitoring_notification_channel.pagerduty module.pim-monitoring.google_monitoring_notification_channel.pagerduty
terraform state mv module.pim.google_monitoring_uptime_check_config.https module.pim-monitoring.google_monitoring_uptime_check_config.https
terraform state mv module.pim.local_file.metric-rendered module.pim-monitoring.local_file.metric-rendered
terraform state mv module.pim.null_resource.metric module.pim-monitoring.null_resource.metric
```

Get the EE tag to deploy :

```
git clone git@github.com:akeneo/pim-enterprise-dev.git && cd pim-enterprise-dev
tag_to_release=$(git fetch origin &> /dev/null && git tag --list | grep -E '^v?[0-9]+$' | sort -r | head -n 1)
```

main.tf
```
terraform {
backend "gcs" {
bucket  = "akecld-terraform"
prefix  = "saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/"
}
}

module "pim" {
source = "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=${tag_to_release}"
google_project_id                 = "akecld-saas-dev"
google_project_zone                 = "europe-west3-a"
instance_name                       = "${INSTANCE_NAME}"
dns_external                        = "${INSTANCE_NAME}.dev.cloud.akeneo.com."
dns_internal                        = "europe-west3-a-akecld-saas-dev.dev.cloud.akeneo.com."
dns_zone                            = "dev-cloud-akeneo-com"
google_storage_location             = "eu"
papo_project_code                   = "NOT_ON_PAPO_srnt-${INSTANCE_NAME}"
force_destroy_storage               = true
pim_version                         = "master"
}

module "pim-monitoring" {
source = "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform/monitoring?ref=${tag_to_release}"
google_project_id                 = "akecld-saas-dev"
instance_name                       = "${INSTANCE_NAME}"
dns_external                        = "${INSTANCE_NAME}.dev.cloud.akeneo.com."
pager_duty_service_key              = "d55f85282a8e4e16b2c822249ad440bd"
}
```

`terraform init -upgrade`
`terraform apply`


# Migrate PIM to 4.x :

Downscale "pim-web & pim-daemons" containers to 0, and remove cronjob :

`kubectl delete -n srnt-${INSTANCE_NAME} cronjob --all`
`kubectl scale -n srnt-${INSTANCE_NAME} deploy/pim-web deploy/pim-daemon --replicas=0`

Get the EE tag to deploy :

```
git clone git@github.com:akeneo/pim-enterprise-dev.git && cd pim-enterprise-dev
tag_to_release=$(git fetch origin &> /dev/null && git tag --list | grep -E '^v?[0-9]+$' | sort -r | head -n 1)
```

- In main.tf :

Change source with

`git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=${tag_to_release}`

Add field in module "PIM"

`pim_version = "${tag_to_release}"`

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

`kubectl scale -n srnt-${INSTANCE_NAME} deploy/pim-web deploy/pim-daemon-default --replicas=2`

In values.yaml, remove the "hook" part
