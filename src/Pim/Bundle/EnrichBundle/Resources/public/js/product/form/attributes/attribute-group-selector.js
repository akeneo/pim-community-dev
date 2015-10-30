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
        'backbone',
        'underscore',
        'pim/form',
        'oro/mediator',
        'pim/attribute-group-manager',
        'text!pim/template/product/tab/attribute/attribute-group-selector',
        'pim/user-context',
        'pim/i18n'
    ],
    function ($, Backbone, _, BaseForm, mediator, AttributeGroupManager, template, UserContext, i18n) {
        return BaseForm.extend({
            tagName: 'ul',
            className: 'nav nav-tabs attribute-group-selector',
            template: _.template(template),
            attributeGroups: [],
            badges: {},
            events: {
                'click li': 'change'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                this.badges          = {};
                this.attributeGroups = [];

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.onValidationError);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.onPostFetch);

                return BaseForm.prototype.configure.apply(this, arguments);
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
                    attributeGroups: _.sortBy(this.getAttributeGroups(), 'sortOrder'),
                    badges: this.badges,
                    locale: UserContext.get('catalogLocale'),
                    i18n: i18n
                }));

                this.delegateEvents();

                return this;
            },

            /**
             * Get attribute list and update it
             *
             * @param {Object} product
             *
             * @return {Promise}
             */
            updateAttributeGroups: function (product) {
                return AttributeGroupManager.getAttributeGroupsForProduct(product)
                    .then(function (attributeGroups) {
                        this.attributeGroups = attributeGroups;
                        this.ensureDefault();

                        return this.getAttributeGroups();
                    }.bind(this));
            },

            /**
             * On attribute group change
             *
             * @param {Event} event
             */
            change: function (event) {
                this.setCurrent(event.currentTarget.dataset.attributeGroup);
            },

            /**
             * Get current attribute group
             *
             * @return {string}
             */
            getCurrent: function () {
                return sessionStorage.getItem('current_attribute_group');
            },

            /**
             * Set current attribute group
             *
             * @param {srting} current
             * @param {Object} options
             */
            setCurrent: function (current, options) {
                options = options || {silent: false};

                if (current !== this.getCurrent()) {
                    sessionStorage.setItem('current_attribute_group', current);

                    if (!options.silent) {
                        this.trigger('attribute-group:change');
                        this.render();
                    }
                }
            },

            /**
             * Ensure default values for the current attribute group
             */
            ensureDefault: function () {
                if (_.isUndefined(this.getCurrent()) ||
                    !this.getAttributeGroups()[this.getCurrent()]
                ) {
                    this.setCurrent(_.first(_.keys(this.getAttributeGroups())), {silent: true});
                }
            },

            /**
             * Get the current attribute group
             *
             * @return {string}
             */
            getCurrentAttributeGroup: function () {
                return this.getAttributeGroups()[this.getCurrent()];
            },

            /**
             * Get all attribute groups
             *
             * @return {object}
             */
            getAttributeGroups: function () {
                return this.attributeGroups;
            },

            /**
             * Increment count on attribute group for the given code
             *
             * @param {string} attributeGroup
             * @param {string} code
             */
            addToBadge: function (attributeGroup, code) {
                if (!this.badges[attributeGroup]) {
                    this.badges[attributeGroup] = {};
                }
                if (!this.badges[attributeGroup][code]) {
                    this.badges[attributeGroup][code] = 0;
                }

                this.badges[attributeGroup][code]++;

                this.render();
            },

            /**
             * Remove badge for the given attribute group
             *
             * @param {string} attributeGroup
             * @param {string} code
             */
            removeBadge: function (attributeGroup, code) {
                delete this.badges[attributeGroup][code];

                this.render();
            },

            /**
             * Remove badges for all attribute groups
             *
             * @param {string} code
             */
            removeBadges: function (code) {
                if (!code) {
                    this.badges = {};
                } else {
                    _.each(this.badges, function (badge) {
                        delete badge[code];
                    }.bind(this));
                }

                this.render();
            }
        });
    }
);
