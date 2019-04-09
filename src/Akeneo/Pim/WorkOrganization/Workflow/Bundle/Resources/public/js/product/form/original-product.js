'use strict';
/**
 * Go back to the original product
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'pimee/template/product/original-product',
        'pim/router',
        'pim/user-context'
    ],
    function (
        _,
        BaseForm,
        template,
        router,
        UserContext
    ) {
        return BaseForm.extend({
            className: 'AknButtonList-item',
            template: _.template(template),
            events: {
                'click .got-to-original': 'goToOriginalProduct'
            },
            configure: function () {
                UserContext.off('change:catalogLocale change:catalogScope', this.render);
                this.listenTo(UserContext, 'change:catalogLocale change:catalogScope', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.getFormData().meta) {
                    return;
                }

                this.$el.html(this.template({}));

                return this;
            },
            goToOriginalProduct: function () {
                router.redirectToRoute(
                    __moduleConfig.urls.product_edit,
                    {
                        id: this.getFormData().meta.original_product_id,
                        dataLocale: UserContext.get('catalogLocale')
                    }
                );
            }
        });
    }
);
