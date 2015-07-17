 'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'text!pimee/template/product/meta/published'
    ],
    function (_, BaseForm, mediator, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            className: 'published-version',
            template: _.template(formTemplate),
            configure: function () {
                mediator.on('product:action:post_update', _.bind(this.render, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
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
