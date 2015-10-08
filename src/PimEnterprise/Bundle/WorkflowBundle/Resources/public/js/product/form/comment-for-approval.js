'use strict';
/**
 * Form to comment when product is sent for approval
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
define(
    [
        'underscore',
        'pim/form',
        'text!pimee/template/product/meta/comment'
    ],
    function (_, BaseForm, template) {
        return BaseForm.extend({
            template: _.template(template),
            render: function () {
                this.$el.html(this.template());

                return this;
            }
        });
    }
);
