# Upgrade from < v20200513XXXXXX  to >= v20200513XXXXXX

- MySQL chart selector were changed, from `namespace-mysql-server-id` to `mysql-id`.  
  Before applying the upgrade, set the *existing* PersistentDisk in MySQL configuration.
  ```bash
  tag_to_release=$(git ls-remote --tags --sort="version:refname" git@github.com:akeneo/pim-enterprise-dev | grep -oE 'v?[0-9]{14}$' | sort -r | head -n 1)

  terraform apply
  ```
  **All the pods will be recreated and MySQL will take longer to re-create as it will wait for existing disk to be releases from former pod.**

- Metrics are now managed by Terraform resources.  
  In order to reimport them inside Terraform state, run:
  ```bash
  terraform import module.pim.google_logging_metric.login_count ${pfid}-login-count
  terraform import module.pim.google_logging_metric.login-response-time-distribution ${pfid}-login-response-time-distribution
  terraform import module.pim.google_logging_metric.logs-count ${pfid}-logs-count

  terraform state rm module.pim.template_file.metric-template
  terraform state rm module.pim.local_file.metric-rendered
  terraform state rm module.pim.null_resource.metric
  ```

# Upgrade fresh serenity instances

In case of upgrade only :

```bash
terraform init -upgrade
terraform state mv module.pim.google_monitoring_alert_policy.alert_policy \
  module.pim-monitoring.google_monitoring_alert_policy.alert_policy

terraform state mv module.pim.google_monitoring_notification_channel.pagerduty \
  module.pim-monitoring.google_monitoring_notification_channel.pagerduty

terraform state mv module.pim.google_monitoring_uptime_check_config.https \
  module.pim-monitoring.google_monitoring_uptime_check_config.https

terraform state mv module.pim.local_file.metric-rendered \
  module.pim-monitoring.local_file.metric-rendered

terraform state mv module.pim.null_resource.metric \
  module.pim-monitoring.null_resource.metric
```

# Migrate PIM to 4.x :

Downscale "pim-web & pim-daemons" containers to 0, and remove cronjobs

```bash
kubectl delete -n srnt-${INSTANCE_NAME} cronjob --all
kubectl scale -n srnt-${INSTANCE_NAME} deploy/pim-web deploy/pim-daemon --replicas=0
```

Get the EE tag to deploy

```bash
git clone git@github.com:akeneo/pim-enterprise-dev.git && cd pim-enterprise-dev
tag_to_release=$(git fetch origin &> /dev/null && git tag --list | grep -E '^v?[0-9]+$' | sort -r | head -n 1)
```

- In main.tf :

Change source with

`git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=${tag_to_release}`

Add field in module "PIM"

`pim_version = "${tag_to_release}"`

- In values.yaml, add in PIM :

```yaml
pim:
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

```bash
terraform init -upgrade
terraform apply
```

Upscale pim-web & pim-daemon :

```bash
kubectl scale -n srnt-${INSTANCE_NAME} deploy/pim-web deploy/pim-daemon-default --replicas=2
```

In values.yaml, *remove* the "hook" section.
