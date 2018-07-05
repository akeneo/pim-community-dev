'use strict';
/**
 * Mass suggest data operation
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/mass-edit-form/product/operation',
        'pimee/template/product/mass-edit/suggest-data'
    ],
    function (
        _,
        __,
        BaseOperation,
        template
    ) {
        return BaseOperation.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    warning: __(this.config.warning, {itemsCount: this.getFormData().itemsCount})
                }));

                return this;
            }
        });
    }
);
