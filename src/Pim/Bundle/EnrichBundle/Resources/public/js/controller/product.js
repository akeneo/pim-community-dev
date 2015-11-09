'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/product-manager',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error'
    ],
    function (_, BaseController, FormBuilder, ProductManager, UserContext, Dialog, PageTitle, Error) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return ProductManager.get(route.params.id)
                    .then(function (product) {
                        PageTitle.set({'product.sku': _.escape(product.meta.label[UserContext.get('catalogLocale')]) });

                        FormBuilder.build(product.meta.form)
                            .then(function (form) {
                                form.setData(product);
                                form.trigger('pim_enrich:form:entity:post_fetch', product);
                                form.setElement(this.$el).render();
                            }.bind(this));
                    }.bind(this))
                .fail(function (response) {
                    var errorView = new Error(response.responseJSON.message, response.status);
                    errorView.setElement(this.$el).render();
                });
            }
        });
    }
);
