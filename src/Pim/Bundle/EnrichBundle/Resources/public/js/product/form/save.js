'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/save',
        'oro/navigation',
        'oro/loading-mask',
        'pim/product-manager'
    ],
    function(
        _,
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
                this.$el.appendTo(this.getRoot().$('header .actions'));
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
                ProductManager.save(100, product).done(_.bind(function(data) {
                    navigation.addFlashMessage('success', 'Product saved');
                    navigation.afterRequest();
                    this.setData(data);
                }, this)).fail(function(response) {
                    console.log('Errors:', response.responseJSON);
                    navigation.addFlashMessage('error', 'Error saving product');
                    navigation.afterRequest();
                }).always(function() {
                    loadingMask.hide().$el.remove();
                });
            }
        });
    }
);
