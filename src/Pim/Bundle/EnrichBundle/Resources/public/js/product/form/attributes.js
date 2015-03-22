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
            state: null,
            events: {
                'click .remove-attribute': 'removeAttribute'
            },
            renderedFields: {},
            initialize: function () {
                FieldManager.fields = {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.getRoot().addTab('attributes', 'Attributes');
                this.state = new Backbone.Model();

                this.listenTo(this.getRoot().model, 'change', this.render);
                this.listenTo(this.state, 'change', this.render);
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
                    this.$el.html(this.template({}));

                    ConfigManager.getEntityList('families').done(_.bind(function(families) {
                        var product = this.getData();
                        var productValues = AttributeGroupManager.getAttributeGroupValues(
                            product,
                            this.extensions['attribute-group-selector'].getCurrentAttributeGroup()
                        );

                        var fieldPromisses = [];
                        _.each(productValues, _.bind(function (productValue, attributeCode) {
                            fieldPromisses.push(this.renderField(product, attributeCode, productValue, families));
                        }, this));

                        $.when.apply($, fieldPromisses).done(_.bind(function() {
                            var $productValuesPanel = this.$('.product-values');

                            this.renderedFields = {};
                            _.each(arguments, _.bind(function(field) {
                                field.render();
                                this.renderedFields[field.attribute.code] = field;
                                $productValuesPanel.append(field.$el);
                            }, this));
                        }, this));
                    }, this));
                    this.delegateEvents();

                    this.renderExtensions();
                }, this));

                return this;
            },
            renderField: function(product, attributeCode, values, families) {
                var promise = $.Deferred();

                FieldManager.getField(attributeCode).done(_.bind(function(field) {
                    field.setContext({
                        'locale': this.state.get('locale'),
                        'scope': this.state.get('scope'),
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

                $.when.apply($, promises).done(_.bind(function() {
                    configurationPromise.resolve();
                }, this));

                return configurationPromise.promise();
            },
            addAttribute: function(attributeCode) {
                var product = this.getData();

                ConfigManager.getEntity('attributes', attributeCode).done(_.bind(function(attribute) {
                    this.extensions['attribute-group-selector'].setCurrent(attribute.group);
                }, this));

                if (product.values[attributeCode]) {
                    this.getRoot().model.trigger('change');
                    return;
                }

                product.values[attributeCode] = [];

                this.extensions['copy'].generateCopyFields();

                this.setData(product);
                this.getRoot().model.trigger('change');
            },
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
                this.state.set('scope', scope);
            },
            getScope: function() {
                return this.state.get('scope');
            },
            setLocale: function(locale) {
                this.state.set('locale', locale);
            },
            getLocale: function() {
                return this.state.get('locale');
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
