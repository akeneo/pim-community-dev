# 2.2.x

## Enhancements

- GITHUB-6943: Update the Docker compose template to run Elasticsearch container in development mode (Thanks [aaa2000](https://github.com/aaa2000)!)

## Bug fixes

- GITHUB-7365: Reference Data Collection doesn't load when attached Entity has multiple cardinalities (Thanks Schwierig!)

## BC breaks

- Change the constructor of `Pim\Component\Connector\Processor\Normalization\ProductProcessor` to add `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Change the constructor of `Pim\Component\Connector\Writer\Database\ProductModelDescendantsWriter` to remove `Pim\Component\Catalog\Builder\ProductBuilderInterface`
    and to add `Akeneo\Component\StorageUtils\Cache\EntityManagerClearerInterface`
- Change the constructor of `Pim\Bundle\DataGridBundle\Datasource\ProductDatasource` to add `Pim\Bundle\DataGridBundle\EventSubscriber\FilterEntityWithValuesSubscriber`
