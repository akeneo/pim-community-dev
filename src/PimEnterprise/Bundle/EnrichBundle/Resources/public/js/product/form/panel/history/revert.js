'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'text!pimee/template/product/panel/history/revert',
        'pim/router',
        'oro/messenger',
        'oro/loading-mask',
        'pim/dialog'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        FetcherRegistry,
        revertTemplate,
        router,
        messenger,
        LoadingMask,
        Dialog
    ) {
        return BaseForm.extend({
            template: _.template(revertTemplate),
            render: function () {
                var $revertAction = $(this.template());
                $revertAction.on('click', this.revert.bind(this));

                this.getParent().addAction('revert', $revertAction);

                return this;
            },
            revert: function (event) {
                event.stopPropagation();

                Dialog.confirm(
                    __('pimee_enrich.entity.product.confirmation.revert.content'),
                    __('pimee_enrich.entity.product.confirmation.revert.title'),
                    function () {
                        var loadingMask = new LoadingMask();
                        loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                        $.get(
                          router.generate(
                              'pimee_versioning_revert_product',
                              { id: $(event.currentTarget).parents('.product-version').data('version-id') }
                          )
                        ).done(
                            function () {
                                // TODO: We shouldn't force product fetching,
                                // we should use request response (cf. send for approval)
                                FetcherRegistry.getFetcher('product').fetch(this.getFormData().meta.id)
                                    .done(function (product) {
                                        loadingMask.hide().$el.remove();
                                        messenger.notificationFlashMessage(
                                            'success',
                                            __('pimee_enrich.entity.product.flash.product_reverted')
                                        );

                                        this.setData(product);

                                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);
                                        this.getRoot().trigger('pim_enrich:form:entity:post_revert', product);
                                    }.bind(this));
                            }.bind(this)
                        ).fail(
                            function (response) {
                                loadingMask.hide().$el.remove();
                                var message = response.responseJSON ? response.responseJSON.error : __('error.common');

                                messenger.notificationFlashMessage('error', message);
                            }
                        );
                    }.bind(this)
                );
            }
        });
    }
);
