'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/download-pdf',
        'routing',
        'pim/user-context'
    ],
    function (
        _,
        BaseForm,
        template,
        Routing,
        UserContext
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            template: _.template(template),
            configure: function () {
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.$el.html(
                    this.template({
                        path: Routing.generate(
                            'pim_pdf_generator_download_product_pdf',
                            {
                                id:         this.getRoot().model.get('meta').id,
                                dataLocale: UserContext.get('catalogLocale'),
                                dataScope:  UserContext.get('catalogScope')
                            }
                        )
                    })
                );

                return this;
            }
        });
    }
);
