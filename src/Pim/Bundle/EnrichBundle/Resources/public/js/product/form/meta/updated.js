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
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var product = this.getFormData();
                var html = '';

                if (product.meta.updated) {
                    html = this.template({
                        label: _.__('pim_enrich.entity.product.meta.updated'),
                        labelBy: _.__('pim_enrich.entity.product.meta.updated_by'),
                        loggedAt: _.result(product.meta.updated, 'logged_at', null),
                        author: _.result(product.meta.updated, 'author', null)
                    });
                }

                this.$el.html(html);

                return this;
            }
        });

        return FormView;
    }
);
