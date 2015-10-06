'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/product-manager',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title'
    ],
    function ($, _, BaseController, FormBuilder, ProductManager, UserContext, Dialog, PageTitle) {

        return BaseController.extend({
            id: 'product-edit-form',
            renderRoute: function (route) {
                return ProductManager.get(route.params.id)
                    .then(_.bind(function (product) {
                        PageTitle.set({'product.sku': _.escape(product.meta.label[UserContext.get('catalogLocale')]) });
                        PageTitle.set({'product.sku': _.escape(product.meta.label[UserContext.get('catalogLocale')]) });

                        FormBuilder.build(product.meta.form)
                            .then(function (form) {
                                form.setData(product);

                                mediator.trigger('pim_enrich:form:entity:post_fetch', product);

                                form.setElement(this.$el).render();
                            });
                    }, this))
                .fail(function (response) {
                    var errorView = new Error(response.responseJSON.message, response.status);
                    errorView.setElement(this.$el).render();
                });
            }
        });
    }
);
