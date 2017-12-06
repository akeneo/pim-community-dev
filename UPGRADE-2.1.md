# UPGRADE FROM 2.0 to 2.1

## Update your indexes

In order to support the new search on published product you need to re-index your published products

```bash
bin/console akeneo:elasticsearch:reset-indexes --env=prod
bin/console pim:product:index --env=prod
bin/console pim:product-model:index --env=prod
bin/console pimee:published-product:index --env=prod
```
