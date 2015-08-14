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
                        label: _.__('pimee_enrich.entity.product.meta.owner_groups') +
                            ': ' +
                            _.reduce(
                                this.getFormData().meta.owner_groups,
                                function (memo, group) {
                                    return memo + ('' !== memo ? ', ' : '') + group.name;
                                },
                                ''
                            )
                    })
                );

                return this;
            }
        });

        return FormView;
    }
);
