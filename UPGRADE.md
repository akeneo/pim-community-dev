# UPGRADE FROM 3.2 TO 4.0

Use this documentation to migrate projects based on the Enterprise Edition.

## Disclaimer

## Requirements

## Migrate your standard project

1. Migrate your MySQL database:

Please, make sure the folder `upgrades/schema/` does not contain former migration files (from PIM 3.1 to 3.2 for instance), otherwise the migration command will surely not work properly.
```bash
cd $PIM_DIR
rm -rf var/cache
bin/console doctrine:migration:migrate --env=prod
```

2. Migrate your Elasticsearch indices

The mapping of the index of the product proposals has changed. So you must rebuild this index by executing these two commands:
```bash
bin/console akeneo:elasticsearch:reset-indexes -i akeneo_pim_product_proposal --env=prod
bin/console pimee:product-proposal:index --env=prod
```
