/* global define */
define(
    [
        'underscore',
        'oro/datagrid/image-cell',
        'pim/template/datagrid/cell/product-and-product-model-image-cell'
    ],
    function (
        _,
        ImageCell,
        productAndProductModelTemplate
    ) {
        'use strict';

        /**
         * Uses a different template if the model is a product_model.
         *
         * @extends oro.datagrid.ImageCell
         */
        return ImageCell.extend({
            productAndProductModelTemplate: _.template(productAndProductModelTemplate),

            /**
             * {@inheritdoc}
             */
            getTemplate(params) {
                if (this.model.get('document_type') === 'product_model') {
                    return this.productAndProductModelTemplate(params);
                }

                return ImageCell.prototype.getTemplate.apply(this, arguments);
            }
        });
    }
);
