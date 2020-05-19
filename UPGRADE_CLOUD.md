# Upgrade from < v20200513XXXXXX  to >= v20200513XXXXXX

- MySQL chart selector were changed, from `namespace-mysql-server-id` to `mysql-id`.  
  Before applying the upgrade, set the *existing* PersistentDisk in MySQL configuration.
  ```bash
  yq d -i values.yaml "mysql.common.persistentDisks"
  for PD in $(kubectl get pv $(kubectl get pvc -l role=mysql-server -o jsonpath='{.items[*].spec.volumeName}') -o jsonpath='{..spec.gcePersistentDisk.pdName}'); do
    yq w -i values.yaml "mysql.common.persistentDisks[+]" "${PD}"
  done
  ```
  **All the pods will be recreated and MySQL will take longer to re-create as it will wait for existing disk to be releases from former pod.**

- Metrics are now managed by Terraform resources.  
  Until Google Provider fix the [metric import issue](https://github.com/terraform-providers/terraform-provider-google/issues/4460), it will require to force project name in pim-monitoring module provider  
  In order to reimport them inside Terraform state, run:
  ```bash
  terraform init
  sed -i.bak 's/var\.google_project_id/"$(GOOGLE_PROJECT_ID)"/g' .terraform/modules/pim-monitoring/main.tf
  terraform import module.pim-monitoring.google_logging_metric.login_count ${pfid}-login-count
  terraform import module.pim-monitoring.google_logging_metric.login-response-time-distribution ${pfid}-login-response-time-distribution
  terraform import module.pim-monitoring.google_logging_metric.logs-count ${pfid}-logs-count
  rm .terraform/modules/pim-monitoring/main.tf && mv .terraform/modules/pim-monitoring/main.tf.bak .terraform/modules/pim-monitoring/main.tf

  terraform state rm module.pim.template_file.metric-template
  terraform state rm module.pim.local_file.metric-rendered
  terraform state rm module.pim.null_resource.metric
  ```

# Migrate from PIM 3.2 to 4.x :

Get the EE tag to deploy

```bash
tag_to_release=$(git ls-remote --tags --sort="version:refname" git@github.com:akeneo/pim-enterprise-dev | grep -oE 'v?[0-9]{14}$' | sort -r | head -n 1)
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
    intermediateUpgrades:
      - v20200211172331
      - v20200401020139
```

- **Follow the other upgrades above** (From older to earlier).

- In values.yaml, *remove* the "hook" section you added before.
