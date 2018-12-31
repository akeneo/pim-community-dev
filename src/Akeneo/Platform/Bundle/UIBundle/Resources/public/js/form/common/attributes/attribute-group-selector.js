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
                this.listenTo(this.getRoot(), 'pim_enrich:form:to-fill:cleared', this.render);
                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:switch_attribute_group',
                    this.setAttributeGroup.bind(this)
                );

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
                    AttributeGroupManager.getAttributeGroupsForObject(this.getFormData())
                ).then(function (attributeGroups) {
                    const scope = UserContext.get('catalogScope');
                    const locale = UserContext.get('catalogLocale');
                    const attributes = toFillFieldProvider.getMissingRequiredFields(this.getFormData(), scope, locale);

                    const toFillAttributeGroups = _.uniq(_.map(attributes, function (attribute) {
                        return AttributeGroupManager.getAttributeGroupForAttribute(
                            attributeGroups,
                            attribute
                        );
                    }));

                    this.$el.empty();
                    this.ensureDefault();
                    if (this.shouldBeDisplayed(this.getElements())) {
                        this.$el.html(this.template({
                            current: this.getCurrent(),
                            elements: _.sortBy(this.getElements(), 'sort_order'),
                            badges: this.badges,
                            locale: UserContext.get('catalogLocale'),
                            toFillAttributeGroups: toFillAttributeGroups,
                            allAttributeCode: this.all.code,
                            currentElement: _.findWhere(this.getElements(), {code: this.getCurrent()}),
                            i18n: i18n,
                            label: __('pim_enrich.entity.attribute_group.uppercase_label')
                        }));
                    }

                    this.delegateEvents();
                }.bind(this));

                return this;
            },

            /**
             * Don't display the dropdown if there is no elements or if the only element is the "All" group.
             *
             * @param {Object} elements
             *
             * @returns {Boolean}
             */
            shouldBeDisplayed: function (elements) {
                const length = Object.keys(elements).length;

                return length > 1 || (1 === length && this.all.code !== Object.values(elements)[0].code);
            },

            /**
             * Set current group from event
             *
             * @param {[type]} attributeGroupCode [description]
             */
            setAttributeGroup: function (attributeGroupCode) {
                this.setCurrent(attributeGroupCode, {silent: true});
            }
        });
    }
);
