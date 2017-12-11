'use strict';

/**
 * Extension to display full screen item picker to choose elements from a grid
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'oro/translator',
        'pim/common/item-picker',
        'pim/fetcher-registry'
    ], function (
        __,
        ItemPicker,
        FetcherRegistry
    ) {
        return ItemPicker.extend({
            // Remove this.config.fetcher

            /**
             * {@inheritdoc}
             */
            selectModel: function (model) {
                this.addItem(model.attributes.document_type + '_' + model.get(this.config.columnName));
            },

            /**
             * {@inheritdoc}
             */
            unselectModel: function (model) {
                this.removeItem(model.attributes.document_type + '_' + model.get(this.config.columnName));
            },

            /**
             * {@inheritdoc}
             */
            updateBasket: function () {
                let productIds = [];
                let productModelIds = [];
                this.getItems().forEach((item) => {
                    const matchProductModel = item.match(/^product_model_(.*)$/);
                    if (matchProductModel) {
                        productModelIds.push(matchProductModel[1]);
                    } else {
                        const matchProduct = item.match(/^product_(.*)$/);
                        productIds.push(matchProduct[1]);
                    }
                });

                $.when(
                    FetcherRegistry.getFetcher('product-model').fetchByIdentifiers(productModelIds),
                    FetcherRegistry.getFetcher('product').fetchByIdentifiers(productIds)
                ).then(function (productModels, products) {
                        this.$('.basket').html(this.basketTemplate({
                            items: products.concat(productModels),
                            title: __('pim_enrich.form.basket.title'),
                            emptyLabel: __('pim_enrich.form.basket.empty_basket'),
                            imagePathMethod: this.imagePathMethod.bind(this),
                            columnName: this.config.columnName,
                            identifierName: this.config.columnName,
                            labelMethod: this.labelMethod.bind(this),
                            itemCodeMethod: this.itemCodeMethod.bind(this),
                        }));

                        this.delegateEvents();
                    }.bind(this));
            },

            itemCodeMethod: function (item) {
                if (item.code) {
                    return 'product_model_' + item.code;
                } else {
                    return 'product_' + item[this.config.columnName];
                }
            }
        });
    }
);
