 'use strict';
/**
 * Created at extension
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
        'text!pim/template/product/meta/created'
    ],
    function (_, BaseForm, formTemplate) {
        var FormView = BaseForm.extend({
            tagName: 'span',
            template: _.template(formTemplate),
            render: function () {
                var product = this.getFormData();
                var html = '';

                if (product.meta.created) {
                    html = this.template({
                        label: _.__('pim_enrich.entity.product.meta.created'),
                        labelBy: _.__('pim_enrich.entity.product.meta.created_by'),
                        loggedAt: _.result(product.meta.created, 'logged_at', null),
                        author: _.result(product.meta.created, 'author', null)
                    });
                }

                this.$el.html(html);

                return this;
            }
        });

        return FormView;
    }
);
