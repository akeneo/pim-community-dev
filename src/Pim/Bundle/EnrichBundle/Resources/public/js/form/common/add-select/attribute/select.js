'use strict';

/**
 * Base add select extension view
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/common/add-select/base/select',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/formatter/choices/base'
    ],
    function (
        $,
        _,
        __,
        BaseAddSelect,
        FetcherRegistry,
        AttributeManager,
        ChoicesFormatter
    ) {
        return BaseAddSelect.extend({
            class: 'AknButtonList-item add-attribute',

            /**
             * {@inheritdoc}
             */
            getItemsToExclude: function () {
                return FetcherRegistry.getFetcher(this.mainFetcher).getIdentifierAttribute()
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
            },

            /**
             * {@inheritdoc}
             */
            prepareChoices: function (items) {
                return _.chain(items).map(function (item) {
                    var group = ChoicesFormatter.formatOne(item.group);
                    var choice = ChoicesFormatter.formatOne(item);
                    choice.group = group;

                    return choice;
                }).value();
            },

            /**
             * Triggers configured event with items codes selected
             */
            addItems: function () {
                this.trigger(this.addEvent, { codes: this.selection });
            },
        });
    }
);

