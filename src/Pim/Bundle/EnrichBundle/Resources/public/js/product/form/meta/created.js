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
                this.$el.html(
                    this.template({
                        product: this.getFormData()
                    })
                );

                return this;
            }
        });

        return FormView;
    }
);
