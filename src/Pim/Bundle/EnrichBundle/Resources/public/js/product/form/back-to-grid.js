'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/back-to-grid',
        'routing',
        'pim/user-context'
    ],
    function (_, BaseForm, template, Routing, UserContext) {
        return BaseForm.extend({
            className: 'btn-group',
            template: _.template(template),
            configure: function () {
                this.listenTo(UserContext, 'change:catalogLocale', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.$el.html(
                    this.template({
                        path: Routing.generate(
                            'pim_enrich_product_index',
                            {
                                dataLocale: UserContext.get('catalogLocale')
                            }
                        )
                    })
                );

                return this;
            }
        });
    }
);
