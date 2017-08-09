'use strict';

define([
    'jquery',
    'underscore',
    'backbone',
    'pim/form',
    'oro/datafilter/product_category-filter'
], function (
    $,
    _,
    Backbone,
    BaseForm,
    CategoryFilter
) {
    return BaseForm.extend({

        /**
         * @inheritDoc
         */
        configure(urlParams) {
            this.urlParams = urlParams;

            return BaseForm.prototype.configure.apply(this, arguments);
        },


        /**
         * @inheritDoc
         */
        render() {
            return new CategoryFilter(
                this.urlParams,
                'product-grid',
                'pim_enrich_categorytree',
                '#tree'
            );
        }
    });
});
