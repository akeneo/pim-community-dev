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
        'module',
        'pim/form',
        'text!pim/template/product/back-to-grid',
        'routing',
        'pim/user-context'
    ],
    function (_, module, BaseForm, template, Routing, UserContext) {
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
                            module.config().gridUrl,
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
