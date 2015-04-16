 'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/mediator',
        'pim/form',
        'pim/field-manager',
        'pim/config-manager',
        'pim/attribute-manager',
        'pim/product-manager',
        'pim/attribute-group-manager',
        'pim/variant-group-manager',
        'pim/user-context',
        'text!pim/template/product/tab/attributes'
    ],
    function (
        $,
        _,
        Backbone,
        mediator,
        BaseForm,
        FieldManager,
        ConfigManager,
        AttributeManager,
        ProductManager,
        AttributeGroupManager,
        VariantGroupManager,
        UserContext,
        formTemplate
    ) {
        var FormView = BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable tabs-left product-attributes',
            events: {
                'click .remove-attribute': 'removeAttribute'
            },
            visibleFields: {},
            initialize: function () {
                FieldManager.fields = {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('attributes', 'Attributes');

                this.listenTo(this.getRoot().model, 'change', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);
                mediator.on('product:action:post_update', _.bind(this.postSave, this));
                mediator.on('product:action:post_validation_error', _.bind(this.postValidationError, this));
                mediator.on('show_attribute', _.bind(this.showAttribute, this));
                window.addEventListener('resize', _.bind(this.resize, this));

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.getConfig().done(_.bind(function () {
                    this.$el.html(this.template({}));
                    this.resize();
                    var product = this.getData();
                    $.when(
                        ConfigManager.getEntityList('families'),
                        ProductManager.getValues(product)
                    ).done(_.bind(function (families, values) {
                        var productValues = AttributeGroupManager.getAttributeGroupValues(
                            values,
                            this.extensions['attribute-group-selector'].getCurrentAttributeGroup()
                        );

                        var fieldPromisses = [];
                        _.each(productValues, _.bind(function (productValue, attributeCode) {
                            fieldPromisses.push(this.renderField(product, attributeCode, productValue, families));
                        }, this));

                        $.when.apply($, fieldPromisses).done(_.bind(function () {
                            var $productValuesPanel = this.$('.product-values');

                            this.visibleFields = {};
                            _.each(arguments, _.bind(function (field) {
                                field.render();
                                this.visibleFields[field.attribute.code] = field;
                                $productValuesPanel.append(field.$el);
                            }, this));
                        }, this));
                    }, this));
                    this.delegateEvents();

                    this.renderExtensions();
                }, this));

                return this;
            },
            resize: function () {
                var productValuesContainer = this.$('.product-values');
                if (productValuesContainer && this.getRoot().$el.length) {
                    productValuesContainer.css(
                        {'height': ($(window).height() - productValuesContainer.offset().top - 4) + 'px'}
                    );
                }
            },
            renderField: function (product, attributeCode, values, families) {
                var promise = $.Deferred();

                FieldManager.getField(attributeCode).done(_.bind(function (field) {
                    field.setContext({
                        'locale': UserContext.get('catalogLocale'),
                        'scope': UserContext.get('catalogScope'),
                        'optional': AttributeManager.isOptional(attributeCode, product, families)
                    });
                    field.setValues(values);

                    promise.resolve(field);
                }, this));

                return promise.promise();
            },
            getConfig: function () {
                var configurationPromise = $.Deferred();
                var promises = [];

                var product = this.getData();

                ConfigManager.getConfig();
                promises.push(this.extensions['attribute-group-selector'].updateAttributeGroups(product));
                promises.push(this.extensions['add-attribute'].updateOptionalAttributes(product));

                $.when.apply($, promises).done(_.bind(function () {
                    configurationPromise.resolve();
                }, this));

                return configurationPromise.promise();
            },
            addAttributes: function (attributeCodes) {
                var product = this.getData();

                var hasRequiredValues = true;
                _.each(attributeCodes, function (attributeCode) {
                    if (!product.values[attributeCode]) {
                        product.values[attributeCode] = [];
                        hasRequiredValues = false;
                    }
                });

                ConfigManager.getEntity('attributes', _.first(attributeCodes)).done(_.bind(function (attribute) {
                    this.extensions['attribute-group-selector'].setCurrent(attribute.group);
                }, this));

                if (hasRequiredValues) {
                    this.getRoot().model.trigger('change');
                    return;
                }

                /* jshint sub:true */
                /* jscs:disable requireDotNotation */
                this.extensions['copy'].generateCopyFields();

                this.setData(product);
                this.getRoot().model.trigger('change');
            },
            removeAttribute: function (event) {
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getData();
                var fields = FieldManager.getFields();

                this.extensions['add-attribute'].updateOptionalAttributes(product);
                delete product.values[attributeCode];
                delete fields[attributeCode];
                /* jshint sub:true */
                this.extensions['copy'].generateCopyFields();
                /* jscs:enable requireDotNotation */

                this.setData(product);

                this.getRoot().model.trigger('change');
            },
            getValuesData: function () {
                // We will have to decide if we keep this behavior
                // (not sure if getting the field value is the good strategy)
                /* global console */
                console.log(this.getData().values);
                return this.getData().values;
            },
            setScope: function (scope, options) {
                UserContext.set('catalogScope', scope, options);
            },
            getScope: function () {
                return UserContext.get('catalogScope');
            },
            setLocale: function (locale, options) {
                UserContext.set('catalogLocale', locale, options);
            },
            getLocale: function () {
                return UserContext.get('catalogLocale');
            },
            postValidationError: function () {
                this.extensions['attribute-group-selector'].removeBadges();
                _.each(FieldManager.getFields(), function (field) {
                    if (!field.getValid()) {
                        mediator.trigger('show_attribute', {attribute: field.attribute.code});
                        return;
                    }
                });
                this.updateAttributeGroupBadges();
            },
            postSave: function () {
                FieldManager.fields = {};
                this.extensions['attribute-group-selector'].removeBadges();

                this.render();
            },
            updateAttributeGroupBadges: function () {
                var fields = FieldManager.getFields();

                AttributeGroupManager.getAttributeGroupsForProduct(this.getData())
                    .done(_.bind(function (attributeGroups) {
                        _.each(fields, _.bind(function (field) {
                            var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                                attributeGroups,
                                field.attribute.code
                            );

                            if (!field.getValid()) {
                                this.extensions['attribute-group-selector'].addToBadge(attributeGroup, 'invalid');
                            }
                        }, this));
                    }, this));
            },
            showAttribute: function (event) {
                AttributeGroupManager.getAttributeGroupsForProduct(this.getData())
                    .done(_.bind(function (attributeGroups) {
                        var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                            attributeGroups,
                            event.attribute
                        );
                        var needRendering = false;

                        if (event.scope) {
                            this.setScope(event.scope, {silent: true});
                            needRendering = true;
                        }
                        if (event.locale) {
                            this.setLocale(event.locale, {silent: true});
                            needRendering = true;
                        }

                        if (attributeGroup !== this.extensions['attribute-group-selector'].getCurrent()) {
                            this.extensions['attribute-group-selector'].setCurrent(attributeGroup);
                            needRendering = true;
                        }

                        if (needRendering) {
                            this.render();
                        }

                        FieldManager.getFields()[event.attribute].setFocus();
                    }, this));
            }
        });

        return FormView;
    }
);
