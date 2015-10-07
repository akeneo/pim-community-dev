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
                return $.when(
                    ProductManager.get(route.params.id),
                    FormBuilder.build('pim/product-edit-form')
                ).done(_.bind(function (product, form) {
                    PageTitle.set({'product.sku': _.escape(product.meta.label[UserContext.get('catalogLocale')]) });
                    form.setData(product, {silent: true});
                    form.setElement(this.$el).render();
                }, this)).fail(function (response) {
                    switch (response.status) {
                        case 400:
                        case 403:
                            Dialog.alert(_.__('pim_enrich.alert.not_allowed'));
                            break;
                    }
                });
            }
        });
    }
);
