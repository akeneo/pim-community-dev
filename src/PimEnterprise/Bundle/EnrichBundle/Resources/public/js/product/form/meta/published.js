 'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'text!pimee/template/product/meta/published'
    ],
    function ($, _, BaseForm, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            template: _.template(formTemplate),
            render: function () {
                this.$el.html(
                    this.template({
                        product: this.getData()
                    })
                );

                return this;
            }
        });

        return FormView;
    }
);
