'use strict';
/**
 * Updated at extension
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
        'oro/mediator',
        'text!pim/template/product/meta/updated'
    ],
    function (_, BaseForm, mediator, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            template: _.template(formTemplate),
            configure: function () {
                mediator.on('product:action:post_update', _.bind(this.render, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                this.$el.html(
                    this.template({
                        product: this.getData()
                    })
                );

                return this;
            }
        });

        return FormView;
    }
);
