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
        'text!pim/template/form/tab/attribute/attribute-group-selector',
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

                var object = event.sentData;
                var valuesErrors = _.uniq(event.response.values, function (error) {
                    return JSON.stringify(error);
                });

                if (valuesErrors) {
                    AttributeGroupManager.getAttributeGroupsForObject(object)
                        .then(function (attributeGroups) {
                            var globalErrors = [];
                            _.each(valuesErrors, function (error) {
                                if (error.global) {
                                    globalErrors.push(error);
                                }

                                var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                                    attributeGroups,
                                    error.attribute
                                );
                                this.addToBadge(attributeGroup, 'invalid');
                            }.bind(this));

                            // Don't force attributes tab if only global errors
                            if (!_.isEmpty(valuesErrors) && valuesErrors.length > globalErrors.length) {
                                this.getRoot().trigger('pim_enrich:form:show_attribute', _.first(valuesErrors));
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
