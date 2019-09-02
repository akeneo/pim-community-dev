'use strict';

/**
 * Attribute group edit form add attribute select extension view
 *
 * @author   Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product/add-select/attribute',
        'pim/fetcher-registry',
        'pim/formatter/choices/base',
        'pim/common/add-select/line'
    ],
    function (
        $,
        _,
        AddAttributeSelect,
        FetcherRegistry,
        ChoicesFormatter,
        LineView
    ) {
        return AddAttributeSelect.extend({
            lineView: LineView,

            /**
             * Render this extension
             *
             * @return {Object}
             */
            render: function () {
                if (!this.hasRightToAdd()) {
                    return this;
                }

                return AddAttributeSelect.prototype.render.apply(this, arguments);
            },

            /**
             * Creates request according to recieved options
             *
             * @param {Object} options
             */
            onGetQuery: function (options) {
                return FetcherRegistry.getFetcher('attribute').search({
                        identifiers: this.getParent().getOtherAttributes().join(','),
                        rights: 0,
                        search: options.term
                    }).then(this.prepareChoices)
                    .then(function (choices) {
                        options.callback({
                            results: choices,
                            more: false
                        });
                    });
            },

            /**
             * {@inheritdoc}
             */
            prepareChoices: function (items) {
                return _.chain(items).map(function (item) {
                    var choice = ChoicesFormatter.formatOne(item);

                    return choice;
                }).value();
            },

            /**
             * Does the user has right to add an attribute
             *
             * @return {Boolean}
             */
            hasRightToAdd: function () {
                return this.getParent().hasRightToAdd();
            }
        });
    }
);

