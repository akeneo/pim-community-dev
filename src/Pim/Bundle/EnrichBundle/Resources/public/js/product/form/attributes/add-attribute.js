"use strict";

define([
        'backbone',
        'underscore',
        'pim/form',
        'pim/attribute-manager',
        'text!pim/template/product/tab/attribute/add-attribute'
    ],
    function (Backbone, _, BaseForm, AttributeManager, template) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'btn-group add-attribute',
            template: _.template(template),
            state: null,
            product: null,
            events: {
                'click li a': 'addAttribute'
            },
            initialize: function()
            {
                this.state = new Backbone.Model({});
                this.listenTo(this.state, 'change', this.render);
                this.product = null;

                return this;
            },
            render: function()
            {
                this.$el.empty();
                this.$el.html(this.template({
                    attributes: this.state.get('attributes'),
                    locale: this.getParent().state.get('locale')
                }));

                this.delegateEvents();

                return this;
            },
            addAttribute: function(event) {
                this.getParent().addAttribute(event.currentTarget.dataset.attribute);
            },
            updateOptionalAttributes: function(product) {
                var promise = $.Deferred();

                this.product = product;
                AttributeManager.getOptionalAttributes(product)
                    .done(_.bind(function(attributes) {
                        this.state.set('attributes', attributes);

                        promise.resolve(this.state.get('attributes'));
                    }, this));

                return promise.promise();
            }
        });
    }
);
