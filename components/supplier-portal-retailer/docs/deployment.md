# Deployment

## Supplier app

In order to deploy our supplier app, we edited the PIM deployment lifecycle by editing the `deployments/terraform/pim/scripts/copy-web-entrypoint-files-to-httpd-mouting-point.sh` script to also copy Supplier Portal front build into the `web-src` volume.

Here is the process:
1. When the PIM is deployed, terraform applied this template: `deployments/terraform/pim/templates/pim-web.yaml`
2. During the initiation of `pim-web` containers, the bash script `deployments/terraform/pim/scripts/copy-web-entrypoint-files-to-httpd-mouting-point.sh` is called.
3. This bash script copy build front files from `components/supplier-portal-supplier/front/build` to `/web-src/supplier-portal/`
4. And it's done, Jimmy can now access Supplier app under the `/supplier-portal` dir, eg: https://ref-supplier-portal.demo.cloud.akeneo.com/supplier-portal/index.html#/login

## Retailer app

To be completed
