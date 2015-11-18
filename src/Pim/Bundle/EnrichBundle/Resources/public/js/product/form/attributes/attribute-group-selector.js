'use strict';
/**
 * Attribute group selector extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form/common/group-selector',
        'pim/attribute-group-manager',
        'text!pim/template/product/tab/attribute/attribute-group-selector',
        'pim/user-context',
        'pim/i18n'
    ],
    function (_, GroupSelectorForm, AttributeGroupManager, template, UserContext, i18n) {
        return GroupSelectorForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.onValidationError);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.onPostFetch);

                return GroupSelectorForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Triggered on validation error
             *
             * @param {Event} event
             */
            onValidationError: function (event) {
                this.removeBadges();

                var product = event.sentData;
                var valuesErrors = event.response.values;
                if (valuesErrors) {
                    AttributeGroupManager.getAttributeGroupsForProduct(product)
                        .then(function (attributeGroups) {
                            _.each(valuesErrors, function (fieldError, attributeCode) {
                                var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                                    attributeGroups,
                                    attributeCode
                                );
                                this.addToBadge(attributeGroup, 'invalid');
                            }.bind(this));

                            if (!_.isEmpty(valuesErrors)) {
                                this.getRoot().trigger(
                                    'pim_enrich:form:show_attribute',
                                    {attribute: _.first(_.keys(valuesErrors))}
                                );
                            }
                        }.bind(this));
                }
            },

            /**
             * Triggered on post fetch
             */
            onPostFetch: function () {
                this.removeBadges();
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty();
                this.$el.html(this.template({
                    current: this.getCurrent(),
                    elements: this.getElements(),
                    badges: this.badges,
                    locale: UserContext.get('catalogLocale'),
                    i18n: i18n
                }));

                this.delegateEvents();

                return this;
            }
        });
    }
);
