'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'routing',
        'pim/form-builder',
        'pim/user-context',
        'oro/translator',
        'oro/loading-mask',
        'pim/router'
    ],
    function ($, _, Backbone, Routing, FormBuilder, UserContext, __, LoadingMask, router) {
        return {
            /**
             * Opens the modal then instantiates the creation form inside it.
             * This function returns a rejected promise when the popin
             * is canceled and a resolved one when it's validated.
             *
             * @return {Promise}
             */
            openProductModal: function () {
                var deferred = $.Deferred();

                var modal = new Backbone.BootstrapModal({
                    title: __('pim_enrich.entity.product.create_popin.title'),
                    content: '',
                    cancelText: __('pim_enrich.entity.product.create_popin.labels.cancel'),
                    okText: __('pim_enrich.entity.product.create_popin.labels.save'),
                    okCloses: false
                });

                modal.open();

                var modalBody = modal.$('.modal-body');
                modalBody.css('min-height', 150);
                modalBody.css('overflow-y', 'hidden');

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(modalBody).show();

                FormBuilder.build('pim-product-create-form')
                    .then(function (form) {
                        form.setElement(modalBody)
                            .render();

                        modal.on('cancel', function () {
                            deferred.reject();
                            modal.remove();
                        });

                        modal.on('ok', function () {
                            form.save()
                                .done(function (newProduct) {
                                    modal.close();
                                    modal.remove();
                                    deferred.resolve();

                                    router.redirectToRoute(
                                        'pim_enrich_product_edit',
                                        { id: newProduct.meta.id }
                                    );
                                });
                        });
                    }.bind(this));

                return deferred.promise();
            }
        };
    }
);
