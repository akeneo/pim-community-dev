'use strict';
/**
 * Form to add a comment in a notification when the proposal is sent for approval
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pimee/template/product/meta/notification-comment'
    ],
    function (_, Backbone, BaseForm, template) {
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
                this.model.set('comment', this.$('textarea[id="modal-comment"]').val());
            },
            render: function () {
                this.$el.html(
                    this.template({
                        label: _.__('pimee_enrich.entity.product_draft.modal.title')
                    })
                );

                return this;
            }
        });
    }
);
