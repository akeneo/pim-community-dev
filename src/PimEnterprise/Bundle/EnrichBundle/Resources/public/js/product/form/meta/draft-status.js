 'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'text!pimee/template/product/meta/draft-status'
    ],
    function (_, BaseForm, mediator, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            className: 'draft-status',
            template: _.template(formTemplate),
            configure: function () {
                this.listenTo(mediator, 'pim_enrich:form:entity:post_update', this.render);

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
