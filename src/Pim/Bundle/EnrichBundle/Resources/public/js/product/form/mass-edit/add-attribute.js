'use strict';

/**
 * Add attribute extension for mass edit common attributes.
 *
 * It's an override on the "add attribute" extension since we need to reject
 * unique attributes.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/attribute-manager',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/product-edit-form/attributes/add-attribute'
    ],
    function (
        $,
        _,
        AttributeManager,
        UserContext,
        FetcherRegistry,
        BaseAddAttribute
    ) {
        return BaseAddAttribute.extend({
            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.defaultOptions.title = _.__(
                    'pim_enrich.form.product.mass_edit.select_attributes'
                );

                BaseAddAttribute.prototype.initialize.apply(arguments);
            },

            /**
             * {@inheritdoc}
             */
            loadAttributesChoices: function () {
                return $.when(
                    AttributeManager.getAvailableOptionalAttributes(this.getFormData()),
                    FetcherRegistry.getFetcher('attribute-group').fetchAll()
                ).then(function (attributes, attributeGroups) {
                    attributes = _.where(attributes, {unique: 0});
                    this.initializeSelect(attributes, attributeGroups);
                }.bind(this));
            },

            /**
             * Initialize the add attributes select
             *
             * @param attributes
             * @param attributeGroups
             */
            initializeSelect: function (attributes, attributeGroups) {
                this.$('select')
                    .html(this.template({
                        groupedAttributes: this.buildGroupedAttributes(attributes, attributeGroups),
                        locale: UserContext.get('catalogLocale')
                    }))
                    .multiselect('refresh')
                    .next('button').removeAttr('style');
            }
        });
    }
);
