'use strict';

/**
 * Push data to PIM.ai
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
define(
    [
        'underscore',
        'oro/translator',
        'jquery',
        'oro/messenger',
        'pim/form',
        'pimee/template/product/suggest-data-push',
        'routing'
    ],
    function (
        _,
        __,
        $,
        messenger,
        BaseForm,
        template,
        Routing
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click .suggest-data': 'pushData'
            },

            render: function () {
                this.$el.html(
                    this.template({label: __('akeneo_suggest_data.product.edit.btn.push')})
                );

                return this;
            },

            pushData: function () {
                $.ajax({
                    method: 'GET',
                    url: Routing.generate('akeneo_suggest_data_push_product', {productId: this.getFormData().meta.id})
                }).done(function (xhr) {
                    console.log(xhr);
                    messenger.notify(
                        'success',
                        _.__('akeneo_suggest_data.product.edit.flash.success')
                    );
                }).fail(function (xhr) {
                    const response = xhr.responseJSON;
                    let errorMessage = _.__('akeneo_suggest_data.product.edit.flash.error');

                    console.log(xhr);
                    if (!_.isUndefined(response.error)) {
                        errorMessage = response.error;
                    }

                    messenger.notify('error', errorMessage);
                }.bind(this));
            }
        });
    }
);
