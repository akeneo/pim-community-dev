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
        'oro/mediator',
        'routing',
        'pim/form',
        'pim/field-manager',
        'pim/fetcher-registry',
        'pim/attribute-manager',
        'pim/attribute-group-manager',
        'pim/user-context',
        'pim/security-context',
        'pim/template/form/tab/attributes',
        'pim/dialog',
        'oro/messenger',
        'pim/i18n'
    ],
    function (
        $,
        _,
        Backbone,
        mediator,
        Routing,
        BaseForm,
        FieldManager,
        FetcherRegistry,
        AttributeManager,
        AttributeGroupManager,
        UserContext,
        SecurityContext,
        formTemplate,
        Dialog,
        messenger,
        i18n
    ) {
        return BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable object-attributes',
            events: {
                'click .remove-attribute': 'removeAttribute'
            },
            rendering: false,

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: _.__(this.config.tabTitle)
                });

                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:add-attribute:after', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:show_attribute', this.showAttribute);

                FieldManager.clearFields();

                this.onExtensions('comparison:change', this.comparisonChange.bind(this));
                this.onExtensions('group:change', this.render.bind(this));
                this.onExtensions('add-attribute:add', this.addAttributes.bind(this));
                this.onExtensions('copy:copy-fields:after', this.render.bind(this));
                this.onExtensions('copy:select:after', this.render.bind(this));
                this.onExtensions('copy:context:change', this.render.bind(this));
                this.onExtensions('pim_enrich:form:scope_switcher:pre_render', this.initScope.bind(this));
                this.onExtensions('pim_enrich:form:locale_switcher:pre_render', this.initLocale.bind(this));
                this.onExtensions('pim_enrich:form:scope_switcher:change', function (event) {
                    this.setScope(event.scopeCode);
                }.bind(this));
                this.onExtensions('pim_enrich:form:locale_switcher:change', function (event) {
                    this.setLocale(event.localeCode);
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured || this.rendering) {
                    return this;
                }

                this.rendering = true;
                this.$el.html(this.template({}));
                this.getConfig().then(function () {
                    var object = this.getFormData();
                    AttributeManager.getValues(object)
                        .then(function (values) {
                            var attributeGroupValues = AttributeGroupManager.getAttributeGroupValues(
                                values,
                                this.getExtension('attribute-group-selector').getCurrentElement()
                            );

                            var fieldPromises = [];
                            _.each(attributeGroupValues, function (value, attributeCode) {
                                fieldPromises.push(this.renderField(object, attributeCode, value));
                            }.bind(this));

                            this.rendering = false;

                            return $.when.apply($, fieldPromises);
                        }.bind(this)).then(function () {
                            return _.sortBy(arguments, function (field) {
                                return field.attribute.sort_order;
                            });
                        }).then(function (fields) {
                            var $valuesPanel = this.$('.object-values');
                            $valuesPanel.empty();

                            FieldManager.clearVisibleFields();
                            _.each(fields, this.appendField.bind(this, $valuesPanel));
                        }.bind(this));
                    this.delegateEvents();

                    this.renderExtensions();
                }.bind(this));

                return this;
            },

            /**
             * Append a field to the panel
             *
             * @param {jQueryElement} panel
             * @param {Object} field
             *
             */
            appendField: function (panel, field) {
                if (field.canBeSeen()) {
                    field.render();
                    FieldManager.addVisibleField(field.attribute.code);
                    panel.append(field.$el);
                }
            },

            /**
             * Render a single field
             *
             * @param {Object} object
             * @param {String} attributeCode
             * @param {Array} values
             *
             * @return {Promise}
             */
            renderField: function (object, attributeCode, values) {
                return FieldManager.getField(attributeCode).then(function (field) {
                    return $.when(
                        (new $.Deferred().resolve(field)),
                        FetcherRegistry.getFetcher('channel').fetchAll(),
                        AttributeManager.isOptional(field.attribute, object)
                    );
                }).then(function (field, channels, isOptional) {
                    var scope = _.findWhere(channels, { code: UserContext.get('catalogScope') });
                    var catalogLocale = UserContext.get('catalogLocale');

                    field.setContext({
                        locale: catalogLocale,
                        scope: scope.code,
                        scopeLabel: i18n.getLabel(scope.labels, catalogLocale, scope.code),
                        uiLocale: catalogLocale,
                        optional: isOptional,
                        removable: SecurityContext.isGranted(this.config.removeAttributeACL)
                    });

                    field.setValues(values);

                    return field;
                }.bind(this));
            },

            /**
             * Get the configuration needed to load the attribute tab
             *
             * @return {Promise}
             */
            getConfig: function () {
                var promises = [];
                var object = this.getFormData();

                promises.push(AttributeGroupManager.getAttributeGroupsForObject(object)
                    .then(function (attributeGroups) {
                        this.getExtension('attribute-group-selector').setElements(
                            _.indexBy(_.sortBy(attributeGroups, 'sort_order'), 'code')
                        );
                    }.bind(this))
                );

                return $.when.apply($, promises).promise();
            },

            /**
             * Add an attribute to the current attribute list
             *
             * @param {Event} event
             */
            addAttributes: function (event) {
                var attributeCodes = event.codes;

                $.when(
                    FetcherRegistry.getFetcher('attribute').fetchByIdentifiers(attributeCodes),
                    FetcherRegistry.getFetcher('locale').fetchActivated(),
                    FetcherRegistry.getFetcher('channel').fetchAll(),
                    FetcherRegistry.getFetcher('currency').fetchAll()
                ).then(function (attributes, locales, channels, currencies) {
                    var formData = this.getFormData();

                    _.each(attributes, function (attribute) {
                        if (!formData.values[attribute.code]) {
                            formData.values[attribute.code] = AttributeManager.generateMissingValues(
                                [],
                                attribute,
                                locales,
                                channels,
                                currencies
                            );
                        }
                    });

                    this.getExtension('attribute-group-selector').setCurrent(
                        _.first(attributes).group_code
                    );

                    this.setData(formData);

                    this.getRoot().trigger('pim_enrich:form:add-attribute:after');
                }.bind(this));
            },

            /**
             * Remove an attribute from the collection
             *
             * @param {Event} event
             */
            removeAttribute: function (event) {
                if (!SecurityContext.isGranted(this.config.removeAttributeACL)) {
                    return;
                }
                var attributeCode = event.currentTarget.dataset.attribute;
                var formData = this.getFormData();
                var fields = FieldManager.getFields();

                Dialog.confirm(
                    _.__('pim_enrich.confirmation.delete.attribute'),
                    _.__('pim_enrich.confirmation.delete_item'),
                    function () {
                        FetcherRegistry.getFetcher('attribute').fetch(attributeCode).then(function (attribute) {
                            $.ajax({
                                type: 'DELETE',
                                url: this.generateRemoveAttributeUrl(attribute),
                                contentType: 'application/json'
                            }).then(function () {
                                this.triggerExtensions('add-attribute:update:available-attributes');

                                delete formData.values[attributeCode];
                                delete fields[attributeCode];

                                this.setData(formData);

                                this.getRoot().trigger('pim_enrich:form:remove-attribute:after');

                                this.render();
                            }.bind(this)).fail(function () {
                                messenger.notify(
                                    'error',
                                    _.__(this.config.deletionFailed)
                                );
                            });
                        }.bind(this));
                    }.bind(this)
                );
            },

            /**
             * Generate the remove attribute url
             *
             * @return {String}
             */
            generateRemoveAttributeUrl: function (attribute) {
                return Routing.generate(
                    this.config.removeAttributeRoute,
                    {
                        code: this.getFormData().code,
                        attributeId: attribute.id
                    }
                );
            },

            /**
             * Initialize  the scope if there is none, or modify it by reference if there is already one
             *
             * @param {Object} event
             */
            initScope: function (event) {
                if (undefined === this.getScope()) {
                    this.setScope(event.scopeCode, {silent: true});
                } else {
                    event.scopeCode = this.getScope();
                }
            },

            /**
             * Set the current scope
             *
             * @param {String} scope
             * @param {Object} options
             */
            setScope: function (scope, options) {
                UserContext.set('catalogScope', scope, options);
            },

            /**
             * Get the current scope
             */
            getScope: function () {
                return UserContext.get('catalogScope');
            },

            /**
             * Initialize  the locale if there is none, or modify it by reference if there is already one
             *
             * @param {Object} event
             */
            initLocale: function (event) {
                if (undefined === this.getLocale()) {
                    this.setLocale(event.localeCode, {silent: true});
                } else {
                    event.localeCode = this.getLocale();
                }
            },

            /**
             * Set the current locale
             *
             * @param {String} locale
             * @param {Object} options
             */
            setLocale: function (locale, options) {
                UserContext.set('catalogLocale', locale, options);
            },

            /**
             * Get the current locale
             */
            getLocale: function () {
                return UserContext.get('catalogLocale');
            },

            /**
             * Post save actions
             */
            postSave: function () {
                FieldManager.fields = {};
                this.render();
            },

            /**
             * Switch to the given attribute
             *
             * @param {Event} event
             */
            showAttribute: function (event) {
                AttributeGroupManager.getAttributeGroupsForObject(this.getFormData())
                    .then(function (attributeGroups) {
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

                        var displayedAttributes = FieldManager.getFields();

                        if (_.has(displayedAttributes, event.attribute)) {
                            displayedAttributes[event.attribute].setFocus();
                        }
                    }.bind(this));
            },

            /**
             * Toggle the comparison mode
             *
             * @param {Boolean} open
             */
            comparisonChange: function (open) {
                this.$el[open ? 'addClass' : 'removeClass']('comparison-mode');
                this.$el.find('.AknAttributeActions')[open ? 'addClass' : 'removeClass'](
                    'AknAttributeActions--comparisonMode'
                );
            }
        });
    }
);
