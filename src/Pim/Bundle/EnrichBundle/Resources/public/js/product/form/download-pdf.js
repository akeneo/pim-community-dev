'use strict';
/**
 * Download pdf extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'pim/template/product/download-pdf',
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
            tagName: 'a',

            className: 'AknDropdown-menuLink btn-download',

            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            configure: function () {
                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.getFormData().meta) {
                    return;
                }

                this.$el.html(this.template());
                this.$el.attr('href', Routing.generate(
                    'pim_pdf_generator_download_product_pdf',
                    {
                        id:         this.getFormData().meta.id,
                        dataLocale: UserContext.get('catalogLocale'),
                        dataScope:  UserContext.get('catalogScope')
                    }
                ));

                return this;
            }
        });
    }
);
