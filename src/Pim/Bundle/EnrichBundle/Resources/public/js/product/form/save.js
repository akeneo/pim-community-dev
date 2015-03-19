'use strict';

define(
    [
        'underscore',
        'oro/mediator',
        'pim/form',
        'text!pim/template/product/save',
        'oro/navigation',
        'oro/loading-mask',
        'pim/product-manager'
    ],
    function(
        _,
        mediator,
        BaseForm,
        template,
        Navigation,
        LoadingMask,
        ProductManager
    ) {
        return BaseForm.extend({
            className: 'btn-group pull-right',
            template: _.template(template),
            events: {
                'click #save': 'save',
            },
            render: function () {
                this.$el.html(this.template());
                this.delegateEvents();

                return this;
            },
            save: function() {
                var product = this.getData();

                delete product.associations;
                delete product.variant_group;
                delete product.meta;

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                var navigation = Navigation.getInstance();
                mediator.trigger('pre_save');
                ProductManager.save(100, product).done(_.bind(function(data) {
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
            }
        });
    }
);
