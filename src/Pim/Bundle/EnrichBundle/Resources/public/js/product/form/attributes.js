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
        'pim/attribute-group-manager',
        'pim/variant-group-manager',
        'text!pim/template/product/tab/attributes'
    ],
    function(
        $,
        _,
        Backbone,
        mediator,
        BaseForm,
        FieldManager,
        ConfigManager,
        AttributeManager,
        AttributeGroupManager,
        VariantGroupManager,
        formTemplate
    ) {
        var FormView = BaseForm.extend({
            template: _.template(formTemplate),
            className: 'tabbable tabs-left product-attributes',
            events: {
                'click .nav-tabs li': 'changeAttributeGroup',
                'click .add-attribute li a': 'addAttribute',
                'click .remove-attribute': 'removeAttribute'
            },
            renderedFields: {},
            initialize: function () {
                this.config = new Backbone.Model({
                    'attributes': []
                });
                FieldManager.fields = {};

                this.listenTo(this.config, 'change', this.render);

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('attributes', 'Attributes');

                this.listenTo(this.getRoot().model, 'change', this.render);
                mediator.on('post_save', _.bind(this.postSave, this));
                mediator.on('post_validation_error', _.bind(this.postValidationError, this));

                return $.when(
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.getConfig().done(_.bind(function() {
                    var product = this.getData();

                    this.$el.html(this.template({
                        config: this.config.toJSON(),
                        state: this.getRoot().state.toJSON()
                    }));

                    var productValues = AttributeGroupManager.getAttributeGroupValues(
                        product,
                        this.extensions['attribute-group-selector'].getCurrentAttributeGroup()
                    );

                    var fieldPromisses = [];
                    _.each(productValues, _.bind(function (productValue, attributeCode) {
                        fieldPromisses.push(this.renderField(product, attributeCode, productValue));
                    }, this));

                    $.when.apply($, fieldPromisses).done(_.bind(function() {
                        var $productValuesPanel = this.$('#product-values');

                        this.renderedFields = {};
                        _.each(arguments, _.bind(function(field) {
                            field.render();
                            this.renderedFields[field.attribute.code] = field;
                            $productValuesPanel.append(field.$el);
                        }, this));
                    }, this));

                    this.$el.appendTo(this.getRoot().$('.form-container .tab-pane[data-tab="attributes"]'));

                    this.delegateEvents();

                    $('#get-data').off('click').on('click', _.bind(this.getValuesData, this));

                    this.renderExtensions();
                }, this));

                return this;
            },
            renderField: function(product, attributeCode, values) {
                var promise = $.Deferred();

                FieldManager.getField(attributeCode).done(_.bind(function(field) {
                    field.setContext({
                        'locale': this.getRoot().state.get('locale'),
                        'scope': this.getRoot().state.get('scope'),
                        'optional': AttributeManager.isOptional(attributeCode, product, this.config.get('families'))
                    });
                    field.setConfig(this.config.toJSON());
                    field.setValues(values);

                    //this.addVariantInfos(product, field);

                    promise.resolve(field);
                }, this));

                return promise.promise();
            },
            getConfig: function () {
                var configurationPromise = $.Deferred();
                var promises = [];

                var product = this.getData();

                promises.push(this.loadConfiguration());
                promises.push(this.extensions['attribute-group-selector'].updateAttributeGroups(product));
                promises.push(this.extensions['add-attribute'].updateOptionalAttributes(product));
                promises.push(AttributeGroupManager.getAttributeGroupsForProduct(product));

                $.when.apply($, promises).done(_.bind(function() {
                    configurationPromise.resolve(this.config);
                }, this));

                return configurationPromise.promise();
            },
            loadConfiguration: function () {
                var promise = $.Deferred();

                ConfigManager.getConfig().done(_.bind(function(config) {
                    this.config.set(config, {silent: true});

                    promise.resolve();
                }, this));

                return promise.promise();
            },
            changeAttributeGroup: function (event) {
                this.config.set('attributeGroup', event.currentTarget.dataset.attributeGroup);
            },
            addAttribute: function(attributeCode) {
                var product = this.getData();

                this.extensions['attribute-group-selector'].setCurrent(
                    this.config.get('attributes')[attributeCode].group
                );

                if (product.values[attributeCode]) {
                    this.getRoot().model.trigger('change');
                    return;
                }

                product.values[attributeCode] = [];

                this.extensions['copy'].generateCopyFields();

                this.setData(product);
                this.getRoot().model.trigger('change');
            },
            // addVariantInfos: function(product, field) {
            //     if (!product.variant_group) {
            //         return;
            //     }
            //     VariantGroupManager.getVariantGroup(product.variant_group).done(_.bind(function(variantGroup) {
            //         if (variantGroup.values && _.contains(_.keys(variantGroup.values), field.attribute.code)) {

            //             var $element = $(
            //                 '<div><i class="icon-lock"></i>Updated by variant group: ' +
            //                     variantGroup.label[this.getRoot().state.get('locale')] +
            //                 '</div>'
            //             );
            //             field.addElement('footer', 'coming_from_variant_group', $element);
            //         }
            //     }, this));
            // },
            removeAttribute: function(event) {
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getData();
                var fields = FieldManager.getFields();

                this.extensions['add-attribute'].updateOptionalAttributes(product);
                delete product.values[attributeCode];
                delete fields[attributeCode];
                this.extensions['copy'].generateCopyFields();

                this.setData(product);

                this.getRoot().model.trigger('change');
            },
            getValuesData: function () {
                //We will have to decide if we keep this behavior (not sure if getting the field value is the good strategie)
                console.log(this.getData().values);
                return this.getData().values;
            },
            setScope: function(scope) {
                this.getRoot().state.set('scope', scope);
            },
            getScope: function() {
                return this.getRoot().state.get('scope');
            },
            setLocale: function(locale) {
                this.getRoot().state.set('locale', locale);
            },
            getLocale: function() {
                return this.getRoot().state.get('locale');
            },
            postValidationError: function() {
                this.extensions['attribute-group-selector'].removeBadges();
                this.updateAttributeGroupBadges();
            },
            postSave: function() {
                FieldManager.fields = {};

                this.render();
            },
            updateAttributeGroupBadges: function() {
                var fields = FieldManager.getFields();

                AttributeGroupManager.getAttributeGroupsForProduct(this.getData())
                    .done(_.bind(function(attributeGroups) {
                        _.each(fields, _.bind(function(field) {
                            var attributeGroup = AttributeGroupManager.getAttributeGroupForAttribute(
                                attributeGroups,
                                field.attribute.code
                            );

                            if (!field.getValid()) {
                                this.extensions['attribute-group-selector'].addToBadge(attributeGroup, 'invalid');
                            }
                        }, this));
                    }, this));
            }
        });

        return FormView;
    }
);
