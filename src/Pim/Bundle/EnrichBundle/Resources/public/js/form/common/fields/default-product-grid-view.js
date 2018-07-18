/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'jquery',
    'pim/form/common/fields/select',
    'pim/fetcher-registry'
],
function (
    $,
    BaseSelect,
    FetcherRegistry
) {
    return BaseSelect.extend({
        /**
         * {@inheritdoc}
         */
        configure: function () {
            return $.when(
                BaseSelect.prototype.configure.apply(this, arguments),
                FetcherRegistry.getFetcher('datagrid-view').fetchAll({alias: 'product-grid'})
                    .then(function (datagridViews) {
                        this.config.choices = datagridViews;
                    }.bind(this))
            );
        },

        /**
         * @param {Array} datagridViews
         */
        formatChoices: function (datagridViews) {
            return datagridViews.reduce((result, datagridView) => {
                result[datagridView.id] = datagridView.label;
                return result;
            }, {});
        },

        /**
         * {@inheritdoc}
         */
        getModelValue() {
            const value = BaseSelect.prototype.getModelValue.apply(this, arguments);

            return value !== undefined ? value + '' : value;
        },

        /**
         * {@inheritdoc}
         */
        isVisible() {
            return this.config.choices.length > 0;
        }
    });
});