'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pimee/template/product/publish',
        'oro/loading-mask',
        'pim/fetcher-registry',
        'pimee/published-product-manager',
        'pim/router',
        'oro/messenger',
        'pim/dialog'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        LoadingMask,
        FetcherRegistry,
        PublishedProductManager,
        router,
        messenger,
        Dialog
    ) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'click .publish-product:not(.disabled)': 'publish',
                'click .unpublish-product': 'unpublish'
            },
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.getFormData().meta.is_owner) {
                    return this.remove();
                }

                this.$el.html(this.template({
                    product: this.getFormData()
                }));
                this.delegateEvents();

                return this;
            },
            publish: function () {
                Dialog.confirm(
                    __('pimee_enrich.entity.product.confirmation.publish.content'),
                    __('pimee_enrich.entity.product.confirmation.publish.title'),
                    this.doPublish.bind(this),
                    __('pim_menu.item.product')
                );
            },
            unpublish: function () {
                Dialog.confirm(
                    __('pimee_enrich.entity.product.confirmation.unpublish.content'),
                    __('pimee_enrich.entity.product.confirmation.unpublish.title'),
                    this.doUnpublish.bind(this),
                    __('pim_menu.item.published_product')
                );
            },
            doPublish: function () {
                this.togglePublished(true);
            },
            doUnpublish: function () {
                this.togglePublished(false);
            },
            togglePublished: function (publish) {
                var productId   = this.getProductId();
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();

                var method = publish ? PublishedProductManager.publish : PublishedProductManager.unpublish;

                // TODO: We shouldn't force product fetching, we should use request response (cf. send for approval)
                return method(productId)
                    .done(function () {
                        FetcherRegistry.getFetcher('product')
                            .fetch(this.getFormData().meta.id).done(function (product) {
                                loadingMask.hide().$el.remove();
                                messenger.notify(
                                    'success',
                                    __(
                                        'pimee_enrich.entity.product.flash.product_' +
                                        (publish ? 'published' : 'unpublished')
                                    )
                                );

                                this.setData(product);

                                this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);
                                this.getRoot().trigger('pim_enrich:form:entity:post_publish', product);
                            }.bind(this));
                    }.bind(this))
                    .fail(function () {
                        messenger.notify(
                            'error',
                            __(
                                'pimee_enrich.entity.product.flash.product_not_' +
                                (publish ? 'published' : 'unpublished')
                            )
                        );
                    })
                    .always(function () {
                        loadingMask.hide().$el.remove();
                    });
            },
            getProductId: function () {
                return this.getFormData().meta.id;
            }
        });
    }
);
