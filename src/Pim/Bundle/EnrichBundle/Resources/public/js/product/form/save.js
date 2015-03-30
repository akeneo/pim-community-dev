'use strict';

define(
    [
        'underscore',
        'oro/mediator',
        'pim/form',
        'text!pim/template/product/save',
        'oro/navigation',
        'oro/loading-mask',
        'pim/product-manager',
        'pim/field-manager'
    ],
    function(
        _,
        mediator,
        BaseForm,
        template,
        Navigation,
        LoadingMask,
        ProductManager,
        FieldManager
    ) {
        return BaseForm.extend({
            className: 'btn-group',
            template: _.template(template),
            events: {
                'click .save-product': 'save',
            },
            render: function () {
                this.$el.html(this.template());
                this.delegateEvents();

                return this;
            },
            save: function() {
                var product   = this.getData();
                var productId = product.meta.id;

                delete product.variant_group;
                delete product.meta;

                this.removeEmptyAttributes(product);

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                var navigation = Navigation.getInstance();
                mediator.trigger('pre_save');
                ProductManager.save(productId, product).done(_.bind(function(data) {
                    navigation.addFlashMessage('success', 'Product saved');
                    navigation.afterRequest();

                    this.setData(data);
                    mediator.trigger('post_save', data);
                }, this)).fail(function(response) {
                    switch (response.status) {
                        case 400:
                            mediator.trigger('validation_error', response.responseJSON);
                        break;
                        case 500:
                            console.log('Errors:', response.responseJSON);
                        break;
                        default:
                    }

                    navigation.addFlashMessage('error', 'Error saving product');
                    navigation.afterRequest();
                }).always(function() {
                    loadingMask.hide().$el.remove();
                });
            },
            removeEmptyAttributes: function(product) {
                var fields = FieldManager.getFields();
                _.each(product.values, function(value, code) {
                    if (fields[code]) {
                        if (0 === fields[code].getData().length) {
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
