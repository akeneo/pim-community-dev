'use strict';
/**
 * Copy extension override able to copy from product working copy
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/product-edit-form/attributes/copy',
        'pim/fetcher-registry'
    ],
    function (
        $,
        _,
        mediator,
        Copy,
        FetcherRegistry
    ) {
        return Copy.extend({
            sources: ['workingCopy', 'draft'],
            currentSource: '',
            workingCopy: {},

            /**
             * @inheritdoc
             */
            configure: function () {
                this.currentSource = this.sources[0];
                this.listenTo(mediator, 'product:action:post_fetch', this.onProductPostFetch);

                return Copy.prototype.configure.apply(this, arguments);
            },

            /**
            * Event callback called just after product is fetched form backend
            *
            * @param {Object} event
            */
            onProductPostFetch: function (event) {
                this.workingCopy = event.originalProduct;
            },

            /**
             * Return the current product id
             *
             * @returns {number}
             */
            getProductId: function () {
                return this.getData().meta.id;
            },

            /**
             * Retrieve the current draft using the draft fetcher
             *
             * @returns {Promise}
             */
            getDraft: function () {
                return FetcherRegistry.getFetcher('product-draft')
                    .fetchForProduct(this.getProductId());
            },

            /**
            * @inheritdoc
            */
            getSourceData: function () {
                var data = {};
                switch (this.currentSource) {
                    case 'workingCopy':
                        data = this.workingCopy.values;
                        break;
                    case 'draft':
                        data = this.getData();
                        break;
                    default:
                        throw new Error("No source is currently selected to copy from");
                }

                return data;
            },

            /**
            * @inheritdoc
            */
            canBeCopied: function (field) {
                return $.when(
                        this.getDraft(),
                        Copy.prototype.canBeCopied.apply(this, arguments)
                    ).then(_.bind(function (draft, canBeCopied) {
                        return draft.isValueChanged(field, this.locale, this.scope) || canBeCopied;
                    }, this));
            }
        });
    }
);
