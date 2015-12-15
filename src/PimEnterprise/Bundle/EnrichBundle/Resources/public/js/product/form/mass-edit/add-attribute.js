'use strict';

/**
 * Add attribute extension for mass edit common attributes.
 * It's an override of the "add attribute" extension since we need to reject unique attributes.
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
        'pim/mass-product-edit-form/add-attribute'
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
            loadAttributesChoices: function () {
                return $.when(
                    AttributeManager.getAvailableOptionalAttributes(this.getFormData()),
                    FetcherRegistry.getFetcher('attribute-group').fetchAll(),
                    FetcherRegistry.getFetcher('permission').fetchAll()
                ).then(function (attributes, attributeGroups, permissions) {
                    var editableGroupCodes = _.chain(permissions.attribute_groups)
                        .where({edit: true})
                        .pluck('code')
                        .value();

                    var editableAttributes = _.chain(attributes)
                        .where({unique: 0})
                        .filter(function (attribute) {
                            return _.contains(editableGroupCodes, attribute.group);
                        })
                        .value();

                    this.initializeSelect(editableAttributes, attributeGroups);
                }.bind(this));
            }
        });
    }
);
