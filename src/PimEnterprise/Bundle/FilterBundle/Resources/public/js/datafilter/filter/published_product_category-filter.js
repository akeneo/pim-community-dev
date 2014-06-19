define(
    ['jquery', 'underscore', 'oro/datafilter/product_category-filter'],
    function ($, _, ProductCategoryFilter) {
        'use strict';

        /**
         * Published product category filter
         *
         * @author    Nicolas Dupont <nicolas@akeneo.com>
         * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
         *
         * @export  oro/datafilter/published_product_category-filter
         * @class   oro.datafilter.PublishedProductCategoryFilter
         * @extends oro.datafilter.ProductCategoryFilter
         */
        return ProductCategoryFilter.extend({});
    }
);
