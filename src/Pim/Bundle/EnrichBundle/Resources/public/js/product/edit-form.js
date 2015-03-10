"use strict";

define(['jquery', 'underscore', 'backbone', 'routing', 'pim/field-manager'], function($, _, Backbone, Routing, FieldManager) {
    var FormState = Backbone.Model.extend({
        defaults: {
            'locale': 'en_US',
            'scope':  'mobile',
            'currentTab': null,
            'currentAttributeGroup': null,
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
            '<button id="get-data">get data</button>'
        ].join('')),
        events: {
            'change #locale': 'changeLocale',
            'change #scope': 'changeScope',
            'click #get-data': 'getData'
        },
        initialize: function () {
            this.listenTo(this.model, 'change', this.render);
        },
        render: function () {
            this.$el.html(this.template({config: this.config, 'state': this.model.toJSON()}));

            _.each(this.model.get('product').values, _.bind(function (value, attributeCode) {
                var field = FieldManager.getField(attributeCode);

                field.setData(value);
                field.setContext({
                    'locale': this.model.get('locale'),
                    'scope': this.model.get('scope')
                });

                this.$el.append(field.render().$el);
            }, this));

            return this;
        },
        changeLocale: function (event) {
            this.model.set('locale', event.currentTarget.value);
        },
        changeAttributeGroup: function (event) {

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
