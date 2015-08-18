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
            className: 'product-owner-groups',
            template: _.template(formTemplate),
            render: function () {
                this.$el.html(
                    this.template({
                        label: _.__('pimee_enrich.entity.product.meta.owner_groups'),
                        groups: this.getOwnerGroups(this.getFormData())
                    })
                );

                return this;
            },

            /**
             * Get human readable owner groups for the given product
             *
             * @param {Object} product
             *
             * @returns {string}
             */
            getOwnerGroups: function(product) {
                return _.pluck(product.meta.owner_groups, 'name').join(', ');
            }
        });

        return FormView;
    }
);
