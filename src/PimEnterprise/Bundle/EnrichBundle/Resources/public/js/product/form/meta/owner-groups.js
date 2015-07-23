 'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'text!pimee/template/product/meta/owner-groups'
    ],
    function (_, BaseForm, mediator, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            className: 'owner-groups',
            template: _.template(formTemplate),
            render: function () {
                this.$el.html(
                    this.template({
                        product: this.getFormData()
                    })
                );

                return this;
            }
        });

        return FormView;
    }
);
