"use strict";

define(['jquery', 'underscore', 'backbone', 'routing', 'pim/field-manager', 'pim/attribute-group-manager', 'pim/attribute-manager', 'text!pim/template/product/form'], function($, _, Backbone, Routing, FieldManager, AttributeGroupManager, AttributeManager, formTemplate) {
    var FormState = Backbone.Model.extend({
        defaults: {
            'locale': 'en_US',
            'scope':  'mobile',
            'currentTab': null,
            'attributeGroup': 'marketing',
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
        }
    };

    var FormView = Backbone.View.extend({
        tagname: 'div',
        model: FormState,
        config: {
            'locales': [
                {
                    'code': 'fr_FR',
                    'label': 'French'
                },
                {
                    'code': 'en_US',
                    'label': 'English'
                }
            ],
            'channels': [
                {
                    'code': 'mobile',
                    'label': 'Mobile'
                },
                {
                    'code': 'tablet',
                    'label': 'Tablet'
                }
            ]
        },
        template: _.template(formTemplate),
        events: {
            'change #locale': 'changeLocale',
            'change #scope': 'changeScope',
            'click .nav-tabs li': 'changeAttributeGroup',
            'click #add-attribute button': 'addAttribute',
            'click #get-data': 'getData'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
        },
        render: function () {
            var configPromises = [];


            AttributeGroupManager.getAttributeGroups().done(_.bind(function(groups) {
                this.config.attributeGroups = groups;
            }, this));

            AttributeManager.getAttributes().done(_.bind(function(attributes) {
                this.config.attributes = attributes;
            }, this));

            configPromises.push(AttributeGroupManager.getAttributeGroups());
            configPromises.push(AttributeManager.getAttributes());

            $.when.apply($, configPromises).done(_.bind(function() {
                this.$el.html(this.template({config: this.config, 'state': this.model.toJSON()}));

                var values = {};
                _.each(this.model.get('product').values, _.bind(function(value, attributeCode) {
                    if (-1 !== this.config.attributeGroups[this.model.get('attributeGroup')].attributes.indexOf(attributeCode)) {
                        values[attributeCode] = value;
                    }
                }, this));

                var fieldPromisses = [];
                _.each(values, _.bind(function (value, attributeCode) {
                    var promise = new $.Deferred();

                    FieldManager.getField(attributeCode).done(_.bind(function(field) {
                        field.setData(value);
                        field.setContext({
                            'locale': this.model.get('locale'),
                            'scope': this.model.get('scope')
                        });
                        field.render();

                        promise.resolve(field);
                    }, this));

                    fieldPromisses.push(promise.promise());
                }, this));

                $.when.apply($, fieldPromisses).done(_.bind(function() {
                    var $productValuesPanel = this.$('#product-values');

                    _.each(arguments, _.bind(function(field) {
                        console.log(field);
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

            console.log(values);
        }
    });

    $(function() {
        productManager.get(100).done(function(data) {
            var formState = new FormState({'product': data});
            var formView  = new FormView({'model': formState});
            $('#product-edit-form').append(formView.render().$el);
        });
    });
});
