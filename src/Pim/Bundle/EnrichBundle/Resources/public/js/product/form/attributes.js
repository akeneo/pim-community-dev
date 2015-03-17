'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/field-manager',
        'pim/config-manager',
        'pim/attribute-manager',
        'pim/variant-group-manager',
        'text!pim/template/product/tab/attributes'
    ],
    function(
        $,
        _,
        Backbone,
        BaseForm,
        FieldManager,
        ConfigManager,
        AttributeManager,
        VariantGroupManager,
        formTemplate
    ) {
        var FormView = BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable tabs-left',
            id: 'product-attributes',
            events: {
                'click .nav-tabs li': 'changeAttributeGroup',
                'click #add-attribute li a': 'addAttribute',
                'click i.remove-attribute': 'removeAttribute'
            },
            renderedFields: [],
            initialize: function () {
                this.config = new Backbone.Model({
                    'attributeGroups' : [],
                    'optionalAttributes' : [],
                    'attributes': [],
                    'attributeGroup': 'marketing'
                });

                this.listenTo(this.config, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('attributes', 'Attributes');

                this.listenTo(this.getRoot().model, 'change', this.render);

                return $.when(
                    this.loadConfiguration(),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            loadConfiguration: function () {
                var promise = $.Deferred();

                ConfigManager.getConfig().done(_.bind(function(config) {
                    this.config.set(config, {silent: true});

                    promise.resolve();
                }, this));

                return promise.promise();
            },
            render: function () {
                this.getConfig().done(_.bind(function() {
                    var product = this.getData();

                    this.$el.html(this.template({
                        config: this.config.toJSON(),
                        state: this.getRoot().state.toJSON()
                    }));

                    var productValues = this.getAttributeGroupValues(product, this.config.get('attributeGroup'));

                    var fieldPromisses = [];
                    _.each(productValues, _.bind(function (productValue, attributeCode) {
                        fieldPromisses.push(this.renderField(product, attributeCode, productValue));
                    }, this));

                    $.when.apply($, fieldPromisses).done(_.bind(function() {
                        var $productValuesPanel = this.$('#product-values');

                        this.renderedFields = [];
                        _.each(arguments, _.bind(function(field) {
                            this.renderedFields.push(field);
                            $productValuesPanel.append(field.$el);
                        }, this));
                    }, this));

                    this.$el.appendTo(this.getRoot().$('.form-container .tab-pane[data-tab="attributes"]'));

                    this.delegateEvents();

                    $('#get-data').off('click').on('click', _.bind(this.getValuesData, this));

                    return BaseForm.prototype.render.apply(this, arguments);
                }, this));

                return this;
            },
            renderField: function(product, attributeCode, value) {
                var promise = $.Deferred();

                FieldManager.getField(attributeCode).done(_.bind(function(field) {
                    field.setContext({
                        'locale': this.getRoot().state.get('locale'),
                        'scope': this.getRoot().state.get('scope'),
                        'optional': AttributeManager.isOptional(attributeCode, product, this.config.get('families'))
                    });
                    field.setConfig(this.config.toJSON());
                    field.setValues(value);
                    field.render();

                    this.addVariantInfos(product, field);

                    promise.resolve(field);
                }, this));

                return promise.promise();
            },

            getConfig: function () {
                var configurationPromise = $.Deferred();
                var promises = [];

                AttributeManager.getAttributeGroupsForProduct(this.getData())
                    .done(_.bind(function(attributeGroups) {
                        this.config.set('attributeGroups', attributeGroups);
                    }, this));
                AttributeManager.getOptionalAttributes(this.getData())
                    .done(_.bind(function(optionalAttributes) {
                        this.config.set('optionalAttributes', optionalAttributes);
                    }, this));

                promises.push(AttributeManager.getAttributeGroupsForProduct(this.getData()));
                promises.push(AttributeManager.getOptionalAttributes(this.getData()));

                $.when.apply($, promises).done(_.bind(function() {
                    configurationPromise.resolve(this.config);
                }, this));

                return configurationPromise.promise();
            },
            getAttributeGroupValues: function (product, attributeGroup) {
                var values = {};
                _.each(product.values, _.bind(function(productValue, attributeCode) {
                    if (attributeGroup && -1 !== this.config.get('attributegroups')[attributeGroup].attributes.indexOf(attributeCode)) {
                        values[attributeCode] = productValue;
                    }
                }, this));

                return values;
            },
            changeAttributeGroup: function (event) {
                this.config.set('attributeGroup', event.currentTarget.dataset.attributeGroup);
            },
            addAttribute: function(event) {
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getData();

                if (product.values[attributeCode]) {
                    this.getRoot().state.trigger('change');
                    return;
                }

                product.values[attributeCode] = [];

                this.config.set('attributeGroup', this.config.get('attributes')[attributeCode].group);
                this.setData(product);
            },
            addVariantInfos: function(product, field) {
                VariantGroupManager.getVariantGroup(product.variant_group).done(_.bind(function(variantGroup) {
                    if (variantGroup.values && _.contains(_.keys(variantGroup.values), field.attribute.code)) {

                        var $element = $(
                            '<div><i class="icon-lock"></i>Updated by variant group: ' +
                                variantGroup.label[this.getRoot().state.get('locale')] +
                            '</div>'
                        );
                        field.addInfo('footer', 'coming_from_variant_group', $element);
                    }
                }, this));
            },
            removeAttribute: function(event) {
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getData();
                delete product.values[attributeCode];

                this.setData(product);
                this.getRoot().model.trigger('change');
            },
            getValuesData: function () {
                var fields = FieldManager.getFields();
                var values = {};
                _.each(fields, function(field, key) {
                    values[key] = field.getData();
                });
                console.log(values);
                this.setData({
                    values: values,
                    enabled: Math.floor(Math.random()*10) >= 5
                });
            }
        });

        return FormView;
    }
);
