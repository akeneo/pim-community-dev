# 4.0.x

# Technical Improvements

- TIP-1185: Use a single index "product_and_product_model_index" to search on product and product models, instead dedicated product/product model indexes

## BC breaks

- Remove akeneo_pim_product and akeneo_pim_product_model ES indexes and merge into akeneo_pim_product_and_product_model. `ProductQueryBuilder` should be used to search products and `ProductModelQueryBuilder` should be used to search product models. 
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field\IdFilter` to add `$prefix`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer` to remove `$productClient`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer` to remove `$productClient` and `$productModelClient`.
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory` to add:
    - `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface`
    - `Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderWithSearchAggregatorFactory` to add:
    - `Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FilterRegistryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\SorterRegistryInterface`
    - `Akeneo\Tool\Component\StorageUtils\Cursor\CursorFactoryInterface`
    - `Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderOptionsResolverInterface`
- Change constructor of `Akeneo\Pim\Enrichment\Bundle\EventSubscriber` to remove `$productClient` and `$productModelClient`.

### Services

- Update `pim_catalog.factory.product_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.factory.product_model_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_identifier_cursor_from_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_identifier_cursor_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.factory.product_model_identifier_cursor_search_after_size_cursor` to use `akeneo_elasticsearch.client.product_and_product_model`
- Remove `akeneo_elasticsearch.client.product` from `pim_catalog.elasticsearch.indexer.product`
- Remove `akeneo_elasticsearch.client.product` and `akeneo_elasticsearch.client.product_model` from `pim_catalog.elasticsearch.indexer.product_model`
- Update `akeneo.pim.enrichment.follow_up.completeness_widget_query` to use `akeneo_elasticsearch.client.product_and_product_model`
- Remove `akeneo_elasticsearch.client.product` and `akeneo_elasticsearch.client.product_model` from `pim_catalog.event_subscriber.category.update_indexes_on_category_deletion`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_root_categories_with_count_not_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `akeneo.pim.enrichment.category.category_tree.query.list_children_categories_with_count_not_including_sub_categories` to use `akeneo_elasticsearch.client.product_and_product_model`
- Update `pim_catalog.query.product_model_query_builder_search_after_size_factory_external_api` to use `Akeneo\Pim\Enrichment\Component\Product\Query\ProductModelQueryBuilder`
- Update `pim_catalog.query.product_model_query_builder_from_size_factory_external_api` to use `Akeneo\Pim\Enrichment\Component\Product\Query\ProductModelQueryBuilder`
- Update `pim_catalog.query.product_model_query_builder_factory` to use `pim_catalog.query.product_model_query_builder`
- Update `pim_catalog.query.product_model_query_builder_from_size_factory` to use `pim_catalog.query.product_model_query_builder`
- Update `pim_catalog.query.product_model_query_builder_search_after_size_factory` to use `pim_catalog.query.product_model_query_builder`
- Remove `pim_catalog.query.product_and_product_model_query_builder_factory.with_product_and_product_model_cursor`
- Update `akeneo.pim.enrichment.query.product_query_builder_from_size_factory.with_product_identifier_cursor` to use `Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductAndProductModelQueryBuilderFactory`
- Update `pim_enrich.query.product_query_builder_from_size_factory.with_product_and_product_model_from_size_cursor` to use `pim_catalog.query.elasticsearch.product_and_model_query_builder_factory`
- Update `pim_enrich.query.product_query_sequential_edit_builder_factory` to use `pim_catalog.query.product_and_product_model_query_builder.class`
