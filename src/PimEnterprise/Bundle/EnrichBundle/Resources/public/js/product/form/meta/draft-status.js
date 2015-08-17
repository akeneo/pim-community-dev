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
                var product = this.getFormData();

                this.$el.html(
                    this.template({
                        label: _.__('pimee_enrich.entity.product.meta.draft_status'),
                        isOwner: product.meta.is_owner,
                        draftStatus: this.getDraftStatus(product)
                    })
                );

                return this;
            },

            /**
             * Get the human readable draft status
             *
             * @param {Object} product
             *
             * @returns {string}
             */
            getDraftStatus: function(product) {
                var status;

                switch (product.meta.draft_status) {
                    case 0:
                        status = _.__('pimee_enrich.entity.product.meta.draft.in_progress')
                        break;
                    case 1:
                        status = _.__('pimee_enrich.entity.product.meta.draft.sent_for_approval')
                        break;
                    default:
                        status = _.__('pimee_enrich.entity.product.meta.draft.working_copy')
                        break;
                }

                return status;
            }
        });

        return FormView;
    }
);
