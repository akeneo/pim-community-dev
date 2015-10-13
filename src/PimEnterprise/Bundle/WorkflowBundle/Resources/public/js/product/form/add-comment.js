'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pimee/template/product/form/add-comment',
        'pim/user-context',
        'pim/i18n'
    ],
    function (_, Backbone, BaseForm, template, UserContext, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'change textarea': 'updateModel'
            },
            initialize: function () {
                this.model = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            updateModel: function () {
                // TODO: count remaining chars

                this.model.set('comment', this.$('textarea[name="comment"]').val());
            },
            render: function () {
                this.$el.html(
                    this.template({
                        label: 'LE LABEL'
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
