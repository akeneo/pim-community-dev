# How to test customer data from the CI from a PR

## Use case 1: I want to check the impact of my migration on SaaS customers

Create a PR on https://github.com/akeneo/pim-enterprise-dev from master with the name of the CE branch (if the migration is set in a CE branch).

Run the workflow `on_demand_check_update_against_client` to check the duration of the upgrade. The duration to check is the upgrade part, corresponding to the  `[TYPE] Upgrade CUSTOMER` job. By default, there are customers corresponding to the most products, product values, product models, or product variants. This list can be changed in the file `pim-enterprise-dev/.circleci/workflows/aliases.yml` by the variable `client-to-clone-srnt` or `client-to-clone-grth`

## Use case 2: I want to check the impact of my migration for a FLEX customer

Create a PR on https://github.com/akeneo/pim-enterprise-dev from master with the name of the CE branch (if the migration is set in a CE branch).

Edit the file `.circleci/workflows/deployments/clones.yml` and comment the trigger `when` for the `on_demand_clone_and_test_migrate_flex_customers` workflow. By default, there are customers corresponding to customers with latest FLEX version and without custom code. This list can be changed in the file `pim-enterprise-dev/.circleci/workflows/aliases.yml` by the variable `client-to-clone-flex`. To change the variable, you will need the project of the client and the name of the instance, which can be found on gcp. The format for the new customer is `INSTANCE_NAME##PROJECT_ID_GCP`
