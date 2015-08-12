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
                        label: _.__('pimee_enrich.entity.product.meta.draft_status') +
                            (this.getFormData().meta.draft_status === 0 ?
                                _.__('pimee_enrich.entity.product.meta.draft.in_progress') :
                                _.__('pimee_enrich.entity.product.meta.draft.sent_for_approval')),
                        product: this.getFormData()
                    })
                );

                return this;
            }
        });

        return FormView;
    }
);
