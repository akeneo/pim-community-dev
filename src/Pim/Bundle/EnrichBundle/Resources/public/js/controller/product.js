'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'pim/controller/base',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error'
    ],
    function (_, __, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FetcherRegistry.getFetcher('product').fetch(route.params.id, {cached: false})
                    .then(function (product) {
                        if (!this.active) {
                            return;
                        }

                        PageTitle.set({'product.sku': product.meta.label[UserContext.get('catalogLocale')] })

                        return FormBuilder.build(product.meta.form)
                            .then(function (form) {
                                this.on('pim:controller:can-leave', function (event) {
                                    form.trigger('pim_enrich:form:can-leave', event);
                                });
                                form.setData(product);

                                form.setElement(this.$el).render();
                            }.bind(this));
                    }.bind(this))
                .fail(function (response) {
                    var message = response.responseJSON ? response.responseJSON.message : __('error.common');

                    var errorView = new Error(message, response.status);
                    errorView.setElement(this.$el).render();
                });
            }
        });
    }
);
