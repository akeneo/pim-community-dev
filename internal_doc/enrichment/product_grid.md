# Product Grid Extensibility 

This documentation explains the different steps to add a new property into the product grid.

In this example, we will explain here how to add a property to inform when a product has been subscribed to an external data provider system.
Product model rows displayed in the datagrid will not have such date as it is not possible to subscribe to this system (for example purpose).

## Requirements

The property to display in the product datagrid must be indexed into the product and product model Elasticsearch index.

# Configuring the datagrid with the new property
 
In your bundle, add the following content in a new file `Resources/config/product.yml`:

```
datagrid:
    product-grid:
        columns:
            is_subscribed_to_data_provider:
                label:         Data Provider Subsription Date
                data_name:     subscribed_date_to_data_provider
                type:          field
```

# Add the property into the read model 

The product grid returns data both from products and product models.
Depending of the entity, the queries are probably not the same. Therefore, you have to implement two different queries:
- one for the product rows
- one for the product model rows

The name of the property must be the same as the name in the `data_name` configuration key in the file `Resources/config/product.yml`.

You have to add the new property in both the product and product models rows.
If one of these entities does not have such property, a `null` value should be added for the property, as it is declared in the datagrid configuration.

## Add the property into the product rows

Create a class. In this example, all products will have a subscribed date of the current time.

Of course, you will probably execute an SQL query or call an external service to fetch the data. 
Please avoid to execute one call per row and try to execute one query to get all data at once, for performance reason.

```
<?php

declare(strict_types=1);

namespace Acme\Bundle\AppBundle\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;

class FetchSubscriptionDateForProductRows implements AddAdditionalProductProperties
{
    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        $rowsWithAdditionalProperty = [];
        
        $currentDate = new \DateTime();
        $formattedCurrentDate= $now->format('c')
        foreach ($rows as $row) {
            $property = new AdditionalProperty('subscribed_date_to_data_provider', $formattedCurrentDate);
            $rowsWithAdditionalProperty[] = $row->addAdditionalProperty($property);
        }

        return $rowsWithAdditionalProperty;
    }
}
```

Then, you have to declare this service and tag it:
```
    acme.query.fetch_subscription_date_for_product_rows:
        class: 'Acme\Bundle\AppBundle\Query\FetchSubscriptionDateForProductRows'
        tags:
            - { name: akeneo.pim.enrichment.product.grid.add_additional_product_properties }
```

## Add the property in the product model rows

In this example, a product model does not have any subscription date to the Data Provider system.
However, adding the property in the product models is mandatory, because it's declared in the datagrid configuration.

So, the property will be set as `null`.

```
<?php

declare(strict_types=1);

namespace Acme\Bundle\AppBundle\Query;

use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\AddAdditionalProductModelProperties;
use Akeneo\Pim\Enrichment\Component\Product\Grid\Query\FetchProductAndProductModelRowsParameters;
use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\AdditionalProperty;

class FetchSubscriptionDateForProductModelRows implements AddAdditionalProductModelProperties
{
    /**
     * {@inheritdoc}
     */
    public function add(FetchProductAndProductModelRowsParameters $queryParameters, array $rows): array
    {
        $rowsWithAdditionalProperty = [];
        foreach ($rows as $row) {
            $property = new AdditionalProperty('subscribed_date_to_data_provider', null);
            $rowsWithAdditionalProperty[] = $row->addAdditionalProperty($property);
        }

        return $rowsWithAdditionalProperty;
    }
}
```

You have to declare this service and tag it:
```
    acme.query.fetch_subscription_date_for_product_model_rows:
        class: 'Acme\Bundle\AppBundle\Query\FetchSubscriptionDateForProductModelRows'
        tags:
            - { name: akeneo.pim.enrichment.product.grid.add_additional_product_model_properties }
```
