'use strict';

define(
    [
        'underscore',
        'backbone',
        'text!pim/template/product/tab/attribute/validation-error',
        'pim/i18n'
    ],
    function (_, Backbone, template, i18n) {
        return Backbone.View.extend({
            template: _.template(template),
            className: 'validation-errors',
            events: {
                'click .change-context': 'changeContext'
            },
            initialize: function (errors, parent) {
                this.errors = errors;
                this.parent = parent;
            },
            render: function () {
                this.$el.html(this.template({errors: this.errors, i18n: i18n}));
                this.delegateEvents();

                return this;
            },
            changeContext: function (event) {
                this.parent.changeContext(event.currentTarget.dataset.locale, event.currentTarget.dataset.scope);
            }
        });
    }
);
