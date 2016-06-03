'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'text!pimee/template/product/publish',
        'oro/navigation',
        'oro/loading-mask',
        'pim/fetcher-registry',
        'pimee/published-product-manager',
        'routing',
        'pim/dialog'
    ],
    function (
        $,
        _,
        BaseForm,
        template,
        Navigation,
        LoadingMask,
        FetcherRegistry,
        PublishedProductManager,
        Routing,
        Dialog
    ) {
        return BaseForm.extend({
            className: 'btn-group btn-dropdown',
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
                    _.__('pimee_enrich.entity.product.confirmation.publish.content'),
                    _.__('pimee_enrich.entity.product.confirmation.publish.title'),
                    this.doPublish.bind(this)
                );
            },
            unpublish: function () {
                Dialog.confirm(
                    _.__('pimee_enrich.entity.product.confirmation.unpublish.content'),
                    _.__('pimee_enrich.entity.product.confirmation.unpublish.title'),
                    this.doUnpublish.bind(this)
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
                var navigation = Navigation.getInstance();

                var method = publish ? PublishedProductManager.publish : PublishedProductManager.unpublish;

                // TODO: We shouldn't force product fetching, we should use request response (cf. send for approval)
                return method(productId)
                    .done(function () {
                        FetcherRegistry.getFetcher('product')
                            .fetch(this.getFormData().meta.id).done(function (product) {
                                navigation.addFlashMessage(
                                    'success',
                                    _.__(
                                        'pimee_enrich.entity.product.flash.product_' +
                                        (publish ? 'published' : 'unpublished')
                                    )
                                );
                                navigation.afterRequest();
                                loadingMask.hide().$el.remove();

                                this.setData(product);

                                this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);
                                this.getRoot().trigger('pim_enrich:form:entity:post_publish', product);
                            }.bind(this));
                    }.bind(this))
                    .fail(function () {
                        navigation.addFlashMessage(
                            'error',
                            _.__(
                                'pimee_enrich.entity.product.flash.product_not_' +
                                (publish ? 'published' : 'unpublished')
                            )
                        );
                        navigation.afterRequest();
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
