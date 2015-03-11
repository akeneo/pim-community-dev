"use strict";

define(['jquery', 'underscore', 'backbone', 'routing', 'pim/field-manager', 'pim/attribute-group-manager'], function($, _, Backbone, Routing, FieldManager, AttributeGroupManager) {
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
        template: _.template([
            '<select id="locale">',
                '<% _.each(config.locales, function (locale) { %>',
                    '<option value="<%= locale.code %>" <%= state.locale === locale.code ? "selected" : "" %>><%= locale.label %></option>',
                '<% }); %>',
            '</select>',
            '<select id="scope">',
                '<% _.each(config.channels, function (scope) { %>',
                    '<option value="<%= scope.code %>" <%= state.scope === scope.code ? "selected" : "" %>><%= scope.label %></option>',
                '<% }); %>',
            '</select>',
            '<select id="attribute-group">',
                '<% _.each(config.attributeGroups, function (attributeGroup) { %>',
                    '<option value="<%= attributeGroup.code %>" <%= state.attributeGroup === attributeGroup.code ? "selected" : "" %>><%= attributeGroup.label.en_US %></option>',
                '<% }); %>',
            '</select>',
            '<button id="get-data">get data</button>'
        ].join('')),
        events: {
            'change #locale': 'changeLocale',
            'change #scope': 'changeScope',
            'change #attribute-group': 'changeAttributeGroup',
            'click #get-data': 'getData'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
        },
        render: function () {
            AttributeGroupManager.getAttributeGroups().done(_.bind(function(groups) {
                this.config.attributeGroups = groups;

                this.$el.html(this.template({config: this.config, 'state': this.model.toJSON()}));

                var values = {};
                _.each(this.model.get('product').values, _.bind(function(value, attributeCode) {
                    if (-1 !== groups[this.model.get('attributeGroup')].attributes.indexOf(attributeCode)) {
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

                $.when(fieldPromisses).done(_.bind(function(promises) {
                    _.each(promises, _.bind(function(promise) {
                        promise.done(_.bind(function(field) {
                            this.$el.append(field.$el);
                        }, this));

                    }, this));
                }, this));
            }, this));

            return this;
        },
        changeLocale: function (event) {
            this.model.set('locale', event.currentTarget.value);
        },
        changeAttributeGroup: function (event) {
            this.model.set('attributeGroup', event.currentTarget.value);
        },
        changeScope: function (event) {
            this.model.set('scope', event.currentTarget.value);
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
        productManager.get(1).done(function(data) {
            var formState = new FormState({'product': data});
            var formView  = new FormView({'model': formState});
            $('#product-edit-form').append(formView.render().$el);
        });
    });
});
