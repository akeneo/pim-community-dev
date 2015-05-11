'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/form',
        'oro/messenger',
        'oro/loading-mask',
        'pim/product-manager',
        'pim/field-manager'
    ],
    function (
        $,
        _,
        mediator,
        BaseForm,
        messenger,
        LoadingMask,
        ProductManager,
        FieldManager
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            configure: function () {
                this.parent.extensions['save-buttons'].addButton({
                    className: 'save-product',
                    priority: 200,
                    label: _.__('pim_enrich.entity.product.btn.save'),
                    events: {
                        'click .save-product': _.bind(this.save, this)
                    }
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            save: function (options) {
                var product = $.extend(true, {}, this.getData());
                var productId = product.meta.id;

                delete product.variant_group;
                delete product.meta;

                this.removeEmptyAttributes(product);

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                mediator.trigger('product:action:pre_save');

                return ProductManager.save(productId, product).done(_.bind(function (data) {
                    messenger.notificationFlashMessage(
                        'success',
                        _.__('pim_enrich.entity.product.info.update_successful')
                    );

                    this.setData(data);
                    if (!options || !options.silent) {
                        mediator.trigger('product:action:post_update', data);
                    }
                }, this)).fail(function (response) {
                    switch (response.status) {
                        case 400:
                            mediator.trigger('validation_error', response.responseJSON);
                            break;
                        case 500:
                            /* global console */
                            console.log('Errors:', response.responseJSON);
                            break;
                        default:
                    }

                    messenger.notificationFlashMessage('error', _.__('pim_enrich.entity.product.info.update_failed'));
                }).always(function () {
                    loadingMask.hide().$el.remove();
                });
            },
            removeEmptyAttributes: function (product) {
                var fields = FieldManager.getFields();
                _.each(product.values, function (value, code) {
                    if (fields[code]) {
                        if (0 === fields[code].getData().length || 'edit' !== fields[code].getEditMode()) {
                            delete product.values[code];
                        } else {
                            value = fields[code].getData();
                        }
                    }
                });
            }
        });
    }
);
