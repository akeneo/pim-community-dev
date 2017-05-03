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
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/group-selector',
        'pim/attribute-group-manager',
        'pim/template/form/tab/attribute/attribute-group-selector',
        'pim/user-context',
        'pim/i18n',
        'pim/provider/to-fill-field-provider'
    ],
    function (
        $,
        _,
        __,
        GroupSelectorForm,
        AttributeGroupManager,
        template,
        UserContext,
        i18n,
        toFillFieldProvider
    ) {
        return GroupSelectorForm.extend({
            tagName: 'div',

            className: 'AknDropdown AknButtonList-item nav nav-tabs group-selector',

            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.onValidationError);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.onPostFetch);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogScope', this.render);

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
                $.when(
                    toFillFieldProvider.getFields(this.getRoot(), this.getFormData()),
                    AttributeGroupManager.getAttributeGroupsForObject(this.getFormData())
                ).then(function (attributes, attributeGroups) {
                    var toFillAttributeGroups = _.uniq(_.map(attributes, function (attribute) {
                        return AttributeGroupManager.getAttributeGroupForAttribute(
                            attributeGroups,
                            attribute
                        );
                    }));

                    this.$el.empty();
                    if (!_.isEmpty(this.getElements())) {
                        this.$el.html(this.template({
                            current: this.getCurrent(),
                            elements: _.sortBy(this.getElements(), 'sort_order'),
                            badges: this.badges,
                            locale: UserContext.get('catalogLocale'),
                            toFillAttributeGroups: toFillAttributeGroups,
                            currentElement: _.findWhere(this.getElements(), {code: this.getCurrent()}),
                            i18n: i18n,
                            label: __('pim_enrich.form.product.tab.attributes.attribute_group_selector')
                        }));
                    }

                    this.delegateEvents();
                }.bind(this));

                return this;
            }
        });
    }
);
