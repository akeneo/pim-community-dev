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
        'oro/translator',
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
        'pim/template/form/tab/attribute/attribute-group',
        'pim/template/common/no-data',
        'pim/provider/to-fill-field-provider',
        'pim/dialog',
        'oro/messenger',
        'pim/i18n'
    ],
    function (
        $,
        _,
        __,
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
        attributeGroupTemplate,
        noDataTemplate,
        toFillFieldProvider,
        Dialog,
        messenger,
        i18n
    ) {
        /**
         * Group field views by sections (attribute groups)
         *
         * @param {Array} attributeGroups
         * @param {Array} fieldsToFill
         *
         * @return {Object}
         */
        const groupFieldsBySection = (attributeGroups, fieldsToFill) => (fieldCollection, field) => {
            const newFieldCollection = Object.assign({}, fieldCollection);

            if (undefined === newFieldCollection[field.attribute.group]) {
                newFieldCollection[field.attribute.group] = {
                    attributeGroup: attributeGroups[field.attribute.group],
                    fields: [],
                    toFill: 0
                };
            }

            newFieldCollection[field.attribute.group].fields.push(field);
            if (-1 !== fieldsToFill.indexOf(field.attribute.code)) {
                newFieldCollection[field.attribute.group].toFill++;
            }

            return newFieldCollection;
        };

        /**
         * Generate a section view for the given fields
         *
         * @param {Object}   fieldCollection
         * @param {function} template
         * @param {String}   label
         *
         * @return {view}
         */
        const createSectionView = (fieldCollection, template, label) => {
            const view = document.createElement('div');
            view.className = 'AknSubsection';
            view.innerHTML = template({
                label,
                fieldCollection,
                __
            });

            const container = fieldCollection.fields.sort(
                (firstField, secondField) => firstField.attribute.sort_order - secondField.attribute.sort_order
            ).reduce((container, field) => {
                _.defer(field.render.bind(field));
                container.appendChild(field.el);

                return container;
            }, document.createElement('div'));

            view.appendChild(container);

            return view;
        };

        return BaseForm.extend({
            template: _.template(formTemplate),
            attributeGroupTemplate: _.template(attributeGroupTemplate),
            noDataTemplate: _.template(noDataTemplate),
            className: 'tabbable object-attributes',
            events: {
                'click .remove-attribute': 'removeAttribute',
                'click .required-attribute-indicator': 'filterRequiredAttributes'
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
                    label: __(this.config.tabTitle)
                });

                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.clearFillFieldProvider);
                this.listenTo(this.getRoot(), 'pim_enrich:form:add-attribute:after', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:show_attribute', this.showAttribute);
                this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:pre_render', this.initScope.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', (scopeEvent) => {
                    if ('base_product' === scopeEvent.context) {
                        this.setScope(scopeEvent.scopeCode, {silent: true});
                        this.clearFillFieldProvider();
                        this.setScope(scopeEvent.scopeCode);
                    }
                });
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:pre_render', this.initLocale.bind(this));
                this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', (localeEvent) => {
                    if ('base_product' === localeEvent.context) {
                        this.setLocale(localeEvent.localeCode, {silent: true});
                        this.clearFillFieldProvider();
                    }
                });

                FieldManager.clearFields();

                this.onExtensions('comparison:change', this.comparisonChange.bind(this));
                this.onExtensions('copy:copy-fields:after', this.render.bind(this));
                this.onExtensions('copy:select:after', this.render.bind(this));
                this.onExtensions('copy:context:change', this.render.bind(this));
                this.onExtensions('group:change', this.render.bind(this));
                this.onExtensions('attribute_filter:change', this.render.bind(this));

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

                var data = this.getFormData();
                AttributeGroupManager.getAttributeGroupsForObject(data)
                    .then((attributeGroups) => {
                        this.getExtension('attribute-group-selector').setElements(
                            _.indexBy(attributeGroups, 'code')
                        );
                        FieldManager.clearVisibleFields();
                    })
                    .then(() => this.filterValues(data.values))
                    .then((values) => this.createFields(data, values))
                    .then((fields) => {
                        this.rendering = false;
                        $.when(
                            AttributeGroupManager.getAttributeGroupsForObject(data)
                        ).then((attributeGroups) => {
                            const scope = UserContext.get('catalogScope');
                            const locale = UserContext.get('catalogLocale');
                            const fieldsToFill = toFillFieldProvider.getMissingRequiredFields(data, scope, locale);

                            const sections = _.values(
                                fields.reduce(groupFieldsBySection(attributeGroups, fieldsToFill), {})
                            ).sort((firstSection, secondSection) =>
                                firstSection.attributeGroup.sort_order - secondSection.attributeGroup.sort_order
                            );
                            const fieldsView = document.createElement('div');

                            for (const section of sections) {
                                fieldsView.appendChild(createSectionView(
                                    section,
                                    this.attributeGroupTemplate,
                                    i18n.getLabel(
                                        section.attributeGroup.labels,
                                        UserContext.get('uiLocale'),
                                        section.attributeGroup.code
                                    )
                                ));
                            }

                            const objectValuesDom = this.$('.object-values').empty();
                            if (_.isEmpty(fields)) {
                                objectValuesDom.append(this.noDataTemplate({
                                    hint: __('oro.datagrid.noresults'),
                                    subHint: __('oro.datagrid.noresults_subTitle'),
                                    imageClass: ''
                                }));
                            } else {
                                objectValuesDom.append(fieldsView);
                            }
                            this.renderExtensions();
                            this.delegateEvents();
                        });
                    });

                if (null !== sessionStorage.getItem('filter_missing_required_attributes')) {
                    sessionStorage.removeItem('filter_missing_required_attributes');
                    this.filterRequiredAttributes();
                }

                return this;
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
            createAttributeField: function (object, attributeCode, values) {
                return FieldManager.getField(attributeCode).then(function (field) {
                    return $.when(
                        (new $.Deferred().resolve(field)),
                        FetcherRegistry.getFetcher('channel').fetchAll(),
                        AttributeManager.isOptional(field.attribute, object)
                    );
                }).then(function (field, channels, isOptional) {
                    var scope = _.findWhere(channels, { code: UserContext.get('catalogScope') });
                    var locale = UserContext.get('catalogLocale');

                    field.setContext({
                        locale,
                        scope: scope.code,
                        scopeLabel: i18n.getLabel(scope.labels, locale, scope.code),
                        uiLocale: UserContext.get('catalogLocale'),
                        optional: isOptional,
                        removable: SecurityContext.isGranted(this.config.removeAttributeACL)
                    });

                    field.setValues(values);
                    FieldManager.addVisibleField(field.attribute.code);

                    return field;
                }.bind(this));
            },

            /**
             * Remove an attribute from the collection
             *
             * @param {Event} event
             *
             * // TODO: Move this to product/form/mass-edit/attributes when the variant groups will be dropped.
             */
            removeAttribute: function (event) {
                if (!SecurityContext.isGranted(this.config.removeAttributeACL)) {
                    return;
                }
                var attributeCode = event.currentTarget.dataset.attribute;
                var formData = this.getFormData();
                var fields = FieldManager.getFields();

                Dialog.confirm(
                    __('pim_enrich.confirmation.delete.attribute'),
                    __('pim_enrich.confirmation.delete_item'),
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
                                    __(this.config.deletionFailed)
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
                        attributeId: attribute.meta.id
                    }
                );
            },

            /**
             * Initialize  the scope if there is none, or modify it by reference if there is already one
             *
             * @param {Object} scopeEvent
             * @param {String} scopeEvent.context
             * @param {String} scopeEvent.scopeCode
             */
            initScope: function (scopeEvent) {
                if ('base_product' === scopeEvent.context) {
                    if (undefined === this.getScope()) {
                        this.setScope(scopeEvent.scopeCode, {silent: true});
                    } else {
                        scopeEvent.scopeCode = this.getScope();
                    }
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
             * @param {Object} eventLocale
             * @param {String} eventLocale.context
             * @param {String} eventLocale.localeCode
             */
            initLocale: function (eventLocale) {
                if ('base_product' === eventLocale.context) {
                    if (undefined === this.getLocale()) {
                        this.setLocale(eventLocale.localeCode, {silent: true});
                    } else {
                        eventLocale.localeCode = this.getLocale();
                    }
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
                FieldManager.clearFields();
                this.render();
            },

            /**
             * Switch to the given attribute
             *
             * @param {Event} event
             */
            showAttribute: function (event) {
                this.getRoot().trigger('pim_enrich:form:form-tabs:change', this.code);

                var needRendering = false;
                if (event.scope) {
                    this.setScope(event.scope, {silent: true});
                    needRendering = true;
                }
                if (event.locale) {
                    this.setLocale(event.locale, {silent: true});
                    needRendering = true;
                }

                if (needRendering) {
                    this.render();
                }

                var displayedAttributes = FieldManager.getFields();

                if (_.has(displayedAttributes, event.attribute)) {
                    const field = displayedAttributes[event.attribute];
                    // TODO: the manager shouldn't be stateful, access the field by another way
                    _.defer(field.setFocus.bind(field));
                }
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
            },

            /**
             * Filter values
             *
             * @param {Object} values
             *
             * @return {Promise}
             */
            filterValues: function (values) {
                if (!this.getExtension('attribute-group-selector').isAll()) {
                    const filteredValues = {};
                    const attributeGroup = this.getExtension('attribute-group-selector').getCurrentElement();
                    attributeGroup.attributes.forEach((attributeCode) => {
                        if (undefined !== values[attributeCode]) {
                            filteredValues[attributeCode] = values[attributeCode];
                        }
                    });
                    values = filteredValues;
                }

                if (undefined === this.getExtension('attribute-filter')) {
                    return $.Deferred().resolve(values);
                }

                return this.getExtension('attribute-filter').filterValues(values);
            },

            /**
             * Render all fields and return a collection of promises
             *
             * This method is pretty optimization oriented: We fetch the attributes as a collection
             * to avoid individual fetching afterward. We also don't use fat arrow functions because
             * we cannot get the 'arguments' object out of it
             *
             * @param {Object} data
             * @param {Object} values
             *
             * @return {Promise}
             */
            createFields: function (data, values) {
                return FetcherRegistry.getFetcher('attribute')
                    .fetchByIdentifiers(Object.keys(values))
                    .then((attributes) => {
                        return $.when.apply($, attributes.map((attribute) => {
                            return this.createAttributeField(data, attribute.code, values[attribute.code]);
                        }));
                    }).then(function () {
                        return _.values(arguments);
                    });
            },

            /**
             * Filter the required attributes and attribute group
             */
            filterRequiredAttributes: function () {
                this.getRoot().trigger('pim_enrich:form:switch_values_filter', 'missing_required');
            },

            /**
             * Clear the fill field provider on product fetch
             */
            clearFillFieldProvider: function () {
                toFillFieldProvider.clear();

                this.getRoot().trigger('pim_enrich:form:to-fill:cleared')
            }
        });
    }
);
