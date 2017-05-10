define([
    'jquery',
    'underscore',
    'routing',
    'oro/datagrid-builder',
    'oro/pageable-collection',
    'pim/datagrid/state',
    'oro/datafilter/product_category-filter',
    'pim/user-context',
    'pim/fetcher-registry',
    'pim/form-builder',
    'pim/product-create',
    'jquery.sidebarize',
    'pim/base-fetcher'
], function(
    $,
    _,
    Routing,
    DatagridBuilder,
    PageableCollection,
    DatagridState,
    ProductCategoryFilter,
    UserContext,
    FetcherRegistry,
    FormBuilder,
    ProductCreate,
    Sidebarize,
    BaseFetcher
) {
    return {
        jquery: $,
        'underscore': _,
        'routing': Routing,
        'oro/datagrid-builder': DatagridBuilder,
        'oro/pageable-collection': PageableCollection,
        'pim/datagrid/state': DatagridState,
        'oro/datafilter/product_category-filter': ProductCategoryFilter,
        'pim/user-context': UserContext,
        'pim/fetcher-registry': FetcherRegistry,
        'pim/form-builder': FormBuilder,
        'pim/product-create': ProductCreate,
        'jquery.sidebarize': Sidebarize,
        'pim/base-fetcher': BaseFetcher
    }
});
