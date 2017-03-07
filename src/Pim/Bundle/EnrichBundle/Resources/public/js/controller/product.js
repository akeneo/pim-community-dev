'use strict';

define(
    [
        'underscore',
        'pim/controller/base',
        'pim/form-builder',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/dialog',
        'pim/page-title',
        'pim/error'
    ],
    function (_, BaseController, FormBuilder, FetcherRegistry, UserContext, Dialog, PageTitle, Error) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             */
            renderRoute: function (route) {
                return FetcherRegistry.getFetcher('product').fetch(route.params.id)
                    .then(function (product) {
                        if (!this.active) {
                            return;
                        }

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
