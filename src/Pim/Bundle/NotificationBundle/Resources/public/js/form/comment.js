'use strict';
/**
 * Form to comment when product is sent for approval
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/comment/comment'
    ],
    function (_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            render: function (context) {
                this.$el.html(this.template(context));

                return this;
            }
        });
    }
);
