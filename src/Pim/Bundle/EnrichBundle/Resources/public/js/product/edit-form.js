"use strict";

define(['jquery', 'underscore', 'backbone', 'routing', 'pim/field-manager', 'pim/config-manager', 'pim/attribute-manager', 'text!pim/template/product/form', 'oro/navigation', 'oro/loading-mask'], function($, _, Backbone, Routing, FieldManager, ConfigManager, AttributeManager, formTemplate, Navigation, LoadingMask) {
    var FormState = Backbone.Model.extend({
        defaults: {
            'locale': 'en_US',
            'scope':  'mobile',
            'currentTab': null,
            'attributeGroup': 'marketing',
            'activeAttributeGroups': {},
            'translationMode': false,
            'panel': null
        }
    });

    var productManager = {
        get: function (id) {
            return $.ajax(
                Routing.generate('pim_enrich_product_rest_get', {id: id}),
                {
                    method: 'GET'
                }
            ).promise();
        },
        save: function (id, data) {
            return $.ajax({
                type: 'POST',
                url: Routing.generate('pim_enrich_product_rest_get', {id: id}),
                contentType: 'application/json',
                data: JSON.stringify(data)
            }).promise();
        }
    };

    var FormView = Backbone.View.extend({
        tagname: 'div',
        model: FormState,
        config: {},
        template: _.template(formTemplate),
        events: {
            'change #locale': 'changeLocale',
            'change #scope': 'changeScope',
            'click .nav-tabs li': 'changeAttributeGroup',
            'click #add-attribute button': 'addAttribute',
            'click #get-data': 'getData',
            'click #save': 'save'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
        },
        render: function () {
            var promises = [];


            ConfigManager.getConfig().done(_.bind(function(config) {
                this.config = config;
            }, this));

            AttributeManager.getAttributeGroupsForProduct(this.model.get('product'))
                .done(_.bind(function(activeAttributeGroups) {
                    this.model.set('activeAttributeGroups', activeAttributeGroups);
                }, this));
            AttributeManager.getOptionalAttributes(this.model.get('product'))
                .done(_.bind(function(optionalAttributes) {
                    this.model.set('optionalAttributes', optionalAttributes);
                }, this));

            promises.push(AttributeManager.getAttributeGroupsForProduct(this.model.get('product')));
            promises.push(AttributeManager.getOptionalAttributes(this.model.get('product')));
            promises.push(ConfigManager.getConfig());

            $.when.apply($, promises).done(_.bind(function() {
                var product = this.model.get('product');

                this.$el.html(this.template({config: this.config, 'state': this.model.toJSON()}));

                var values = {};
                _.each(product.values, _.bind(function(value, attributeCode) {
                    if (-1 !== this.config.attributegroups[this.model.get('attributeGroup')].attributes.indexOf(attributeCode)) {
                        values[attributeCode] = value;
                    }
                }, this));

                var fieldPromisses = [];
                _.each(values, _.bind(function (value, attributeCode) {
                    var promise = new $.Deferred();

                    FieldManager.getField(attributeCode).done(_.bind(function(field) {
                        field.setValues(value);
                        field.setContext({
                            'locale': this.model.get('locale'),
                            'scope': this.model.get('scope')
                        });
                        field.setConfig(this.config);
                        field.render();

                        promise.resolve(field);
                    }, this));

                    fieldPromisses.push(promise.promise());
                }, this));

                $.when.apply($, fieldPromisses).done(_.bind(function() {
                    var $productValuesPanel = this.$('#product-values');

                    _.each(arguments, _.bind(function(field) {
                        $productValuesPanel.append(field.$el);
                    }, this));
                }, this));
            }, this));

            return this;
        },
        changeLocale: function (event) {
            this.model.set('locale', event.currentTarget.value);
        },
        changeAttributeGroup: function (event) {
            this.model.set('attributeGroup', event.currentTarget.dataset.attributeGroup);
        },
        changeScope: function (event) {
            this.model.set('scope', event.currentTarget.value);
        },
        addAttribute: function(event) {
            var attributeCode = $(event.currentTarget).parent().children('select').val();
            var product = this.model.get('product');

            if (product.values[attributeCode]) {
                this.model.trigger('change');
                return;
            }

            product.values[attributeCode] = [];

            this.model.set('product', product);
            this.model.trigger('change');
        },
        getData: function () {
            var fields = FieldManager.getFields();
            var values = {};
            _.each(fields, function(field, key) {
                values[key] = field.getData();
            });

            return {
                values: values,
                enabled: Math.floor(Math.random()*10) >= 5
            };
        },
        save: function() {
            var loadingMask = new LoadingMask();
            var navigation = Navigation.getInstance();
            loadingMask.render().$el.appendTo(this.$el).show();
            productManager.save(100, this.getData()).done(_.bind(function(product) {
                navigation.addFlashMessage('success', 'Product saved');
                navigation.afterRequest();

                this.model.set('product', product);
                this.model.trigger('change');
            }, this)).fail(function(response) {
                console.log('Errors:', response.responseJSON);
                navigation.addFlashMessage('error', 'Error saving product');
                navigation.afterRequest();
            }).always(function() {
                loadingMask.hide().$el.remove();
            });
        }
    });

    $(function() {
        productManager.get(100).done(function(product) {
            var formState = new FormState({'product': product});
            var formView  = new FormView({'model': formState});
            $('#product-edit-form').append(formView.render().$el);
        });
    });
});
