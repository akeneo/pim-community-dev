 'use strict';
/**
 * Attribute tab extension
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
        'backbone',
        'routing',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/attribute-group-manager',
        'pim/user-context',
        'pim/security-context',
        'pim/template/form/tab/attributes'
    ],
    function (
        $,
        _,
        Backbone,
        Routing,
        BaseForm,
        FieldManager,
        FetcherRegistry,
        AttributeManager,
        AttributeGroupManager,
        UserContext,
        SecurityContext,
        formTemplate
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable product-attributes',
            rendering: false,
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__('pim_enrich.form.product.tab.attributes.title')
                });

                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:show_attribute', this.showAttribute);
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:to-fill-filter', this.addFieldFilter);
                this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:pre_render', this.initScope.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', function (scopeEvent) {
                    if ('base_product' === scopeEvent.context) {
                        this.setScope(scopeEvent.scopeCode);
                    }
                }.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:pre_render', this.initLocale.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', function (localeEvent) {
                    if ('base_product' === localeEvent.context) {
                        this.setLocale(localeEvent.localeCode);
                    }
                }.bind(this));

                FieldManager.clearFields();

                this.onExtensions('group:change', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured || this.rendering) {
                    return this;
                }

                this.rendering = true;
                this.getConfig().done(function () {
                    this.$el.html(this.template({}));
                    var product = this.getFormData();
                    AttributeManager.getValues(product).then(function (values) {
                        var productValues = AttributeGroupManager.getAttributeGroupValues(
                            values,
                            this.getExtension('attribute-group-selector').getCurrentElement()
                        );

                        var fieldPromises = [];
                        _.each(productValues, function (productValue, attributeCode) {
                            fieldPromises.push(this.renderField(product, attributeCode, productValue));
                        }.bind(this));

                        this.rendering = false;

                        return $.when.apply($, fieldPromises);
                    }.bind(this)).then(function () {
                        var $productValuesPanel = this.$('.object-values');
                        $productValuesPanel.empty();

                        FieldManager.clearVisibleFields();
                        _.each(arguments, function (field) {
                            if (field.canBeSeen()) {
                                field.render();
                                FieldManager.addVisibleField(field.attribute.code);
                                $productValuesPanel.append(field.$el);
                            }
                        }.bind(this));
                    }.bind(this));
                    this.delegateEvents();

                    this.renderExtensions();
                }.bind(this));

                return this;
            },
            renderField: function (product, attributeCode, values) {
                return FieldManager.getField(attributeCode).then(function (field) {
                    field.setContext({
                        locale: UserContext.get('catalogLocale'),
                        scope: UserContext.get('catalogScope'),
                        uiLocale: UserContext.get('catalogLocale'),
                        optional: false,
                        removable: false
                    });
                    field.setValues(values);

                    return field;
                });
            },
            getConfig: function () {
                var promises = [];
                var product = this.getFormData();

                promises.push(AttributeGroupManager.getAttributeGroupsForObject(product)
                    .then(function (attributeGroups) {
                        this.getExtension('attribute-group-selector').setElements(attributeGroups);
                    }.bind(this))
                );

                return $.when.apply($, promises).promise();
            },

            /**
             * Initialize  the scope if there is none, or modify it by reference if there is already one
             *
             * @param {Object} event
             */
            initScope: function (event) {
                if ('base_product' === event.context) {
                    if (undefined === this.getScope()) {
                        this.setScope(event.scopeCode, {silent: true});
                    } else {
                        event.scopeCode = this.getScope();
                    }
                }
            },

            /**
             * Set the new catalog scope
             *
             * @param scope
             * @param options
             */
            setScope: function (scope, options) {
                UserContext.set('catalogScope', scope, options);
            },

            /**
             * Get the current catalog scope
             */
            getScope: function () {
                return UserContext.get('catalogScope');
            },

            /*
             * Initialize  the locale if there is none, or modify it by reference if there is already one
             *
             * @param {Object} localeEvent
             * @param {String} localeEvent.localeCode
             * @param {String} localeEvent.context
             */
            initLocale: function (localeEvent) {
                if ('base_product' === localeEvent.context) {
                    if (undefined === this.getLocale()) {
                        this.setLocale(localeEvent.localeCode, {silent: true});
                    } else {
                        localeEvent.localeCode = this.getLocale();
                    }
                }
            },

            setLocale: function (locale, options) {
                UserContext.set('catalogLocale', locale, options);
            },
            getLocale: function () {
                return UserContext.get('catalogLocale');
            },
            postSave: function () {
                FieldManager.fields = {};
                this.render();
            },
            showAttribute: function (event) {
                AttributeGroupManager.getAttributeGroupsForObject(this.getFormData())
                    .done(function (attributeGroups) {
                        this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.code);

                        var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                            attributeGroups,
                            event.attribute
                        );
                        var needRendering = false;

                        if (!attributeGroup) {
                            return;
                        }

                        if (event.scope) {
                            this.setScope(event.scope, {silent: true});
                            needRendering = true;
                        }
                        if (event.locale) {
                            this.setLocale(event.locale, {silent: true});
                            needRendering = true;
                        }

                        var attributeGroupSelector = this.getExtension('attribute-group-selector');
                        if (attributeGroup !== attributeGroupSelector.getCurrent()) {
                            attributeGroupSelector.setCurrent(attributeGroup);
                            needRendering = true;
                        }

                        if (needRendering) {
                            this.render();
                        }

                        FieldManager.getFields()[event.attribute].setFocus();
                    }.bind(this));
            },

            /**
             * Add filter on field to make it readonly.
             *
             * @param {object} event
             */
            addFieldFilter: function (event) {
                event.filters.push($.Deferred().resolve(function () {
                    return [];
                }));
            }
        });
    }
);
