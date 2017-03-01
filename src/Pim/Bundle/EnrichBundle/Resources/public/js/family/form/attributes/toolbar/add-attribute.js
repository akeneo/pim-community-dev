'use strict';

/**
 * Add attribute select view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/fetcher-registry',
        'pim/common/add-attribute'
    ],
    function (
        $,
        _,
        FetcherRegistry,
        AddAttribute
    ) {
        return AddAttribute.extend({
            resultsPerPage: 10,
            class: 'AknButtonList-item add-attribute',

            /**
             * {@inheritdoc}
             */
            addAttributes: function () {
                this.getRoot().trigger('add-attribute:add', { codes: this.selection });
            },
            /**
             * {@inheritdoc}
             */
            getExcludedAttributes: function () {
                return FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
                    .then(function (identifier) {
                        var existingAttributes = _.pluck(
                            this.getFormData().attributes,
                            'code'
                        );

                        if (!_.contains(existingAttributes, identifier.code)) {
                            existingAttributes.push(identifier.code);
                        }

                        return existingAttributes;
                    }.bind(this));
            }
        });
    }
);

