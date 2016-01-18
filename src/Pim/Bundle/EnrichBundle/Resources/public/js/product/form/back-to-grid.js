'use strict';
/**
 * Back to grid extension
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
        'text!pim/template/product/back-to-grid',
        'routing',
        'pim/user-context',
        'oro/navigation'
    ],
    function (_, BaseForm, template, Routing, UserContext, Navigation) {
        return BaseForm.extend({
            className: 'btn-group',
            template: _.template(template),
            configure: function () {
                UserContext.off('change:catalogLocale', this.render);
                this.listenTo(UserContext, 'change:catalogLocale', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.$el.html(this.template({
                    path: Routing.generate(
                        'pim_enrich_product_index',
                        {
                            dataLocale: UserContext.get('catalogLocale')
                        }
                    )
                }));

                Navigation.getInstance().processClicks(this.$('a'));

                return this;
            }
        });
    }
);
