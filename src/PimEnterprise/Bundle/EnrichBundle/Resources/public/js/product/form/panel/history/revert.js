'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'pim/fetcher-registry',
        'text!pimee/template/product/panel/history/revert',
        'oro/navigation',
        'oro/loading-mask',
        'routing',
        'pim/dialog'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        FetcherRegistry,
        revertTemplate,
        Navigation,
        LoadingMask,
        Routing,
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
                    _.__('pimee_enrich.entity.product.confirmation.revert.content'),
                    _.__('pimee_enrich.entity.product.confirmation.revert.title'),
                    function () {
                        var navigation = Navigation.getInstance();
                        var loadingMask = new LoadingMask();
                        loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                        $.ajax(
                            Routing.generate('pimee_versioning_revert_product', {
                                id: $(event.currentTarget).parents('.product-version').data('version-id')
                            }),
                            {
                                method: 'GET'
                            }
                        ).done(
                            function () {
                                // TODO: We shouldn't force product fetching,
                                // we should use request response (cf. send for approval)
                                FetcherRegistry.getFetcher('product').fetch(this.getFormData().meta.id).done(function (product) {
                                    navigation.addFlashMessage(
                                        'success',
                                        _.__('pimee_enrich.entity.product.flash.product_reverted')
                                    );
                                    navigation.afterRequest();
                                    loadingMask.hide().$el.remove();

                                    this.setData(product);

                                    this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);
                                    this.getRoot().trigger('pim_enrich:form:entity:post_revert', product);
                                }.bind(this));
                            }.bind(this)
                        ).fail(
                            function (response) {
                                loadingMask.hide().$el.remove();
                                navigation.addFlashMessage('error', response.responseJSON.error);
                                navigation.afterRequest();
                            }
                        );
                    }.bind(this)
                );
            }
        });
    }
);
