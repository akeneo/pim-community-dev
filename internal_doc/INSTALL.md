# Akeneo PIM Enterprise Application


## Installation

### 1. Get the latest tag to deploy

```bash
tag_to_release=$(git ls-remote --tags --sort="version:refname" git@github.com:akeneo/pim-enterprise-dev | grep -oE 'v?[0-9]{14}$' | sort -r | head -n 1)
```

### 2. Create `main.tf`

```hcl
terraform {
  backend "gcs" {
    bucket = "akecld-terraform"
    prefix = "saas/akecld-saas-dev/europe-west3-a/srnt-${INSTANCE_NAME}/"
  }
}

module "pim" {
  source = "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform?ref=${tag_to_release}"

  pim_version             = "${tag_to_release}"
  google_project_id       = "akecld-saas-dev"
  google_project_zone     = "europe-west3-a"
  instance_name           = "${INSTANCE_NAME}"
  dns_external            = "${INSTANCE_NAME}.dev.cloud.akeneo.com."
  dns_internal            = "europe-west3-a-akecld-saas-dev.dev.cloud.akeneo.com."
  dns_zone                = "dev-cloud-akeneo-com"
  google_storage_location = "EU"
  papo_project_code       = "NOT_ON_PAPO_srnt-${INSTANCE_NAME}"
  force_destroy_storage   = true
}

module "pim-monitoring" {
  source = "git@github.com:akeneo/pim-enterprise-dev.git//deployments/terraform/monitoring?ref=${tag_to_release}"

  google_project_id      = "akecld-saas-dev"
  instance_name          = "${INSTANCE_NAME}"
  dns_external           = "${INSTANCE_NAME}.dev.cloud.akeneo.com."
  pager_duty_service_key = "d55f85282a8e4e16b2c822249ad440bd"
}
```

### 3. Create `values.yaml`

```bash
cat << EOF > values.yaml
# For staging uncomment this block and change disk sizes
# backup:
#   snapshot:
#     schedule: "0 0 0 */1 * *" # Once per day
#     retentionPolicy:
#       dailyBackups: 10
#       weeklyBackups: 0
#       monthlyBackups: 0
#       yearlyBackups: 0
pim:
  secret: $(uuidgen)
  defaultAdminUser:
    login : nil # Fill me
    firstName: nil # Fill me
    lastName: nil # Fill me
    email: nil # Fill me
    password: $(pwgen -sB 24)
    uiLocale: en_US # Change me if needs be
  featureflags:
  - FLAG_1
  - FLAG_2

mysql:
  mysql:
    dataDiskSize: 100Gi # Production: 100Gi | Sandbox: 30Gi
    userPassword: $(pwgen -sB 24)
    rootPassword: $(pwgen -sB 24)

elasticsearch:
  master:
    persistence:
      size: 1Gi
  data:
    persistence:
      size: 10Gi
EOF
```

### 4. Apply

```bash
terraform init -upgrade
terraform apply
```
