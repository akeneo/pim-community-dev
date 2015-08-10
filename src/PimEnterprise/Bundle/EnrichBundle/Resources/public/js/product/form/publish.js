'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/mediator',
        'pim/form',
        'text!pimee/template/product/publish',
        'oro/navigation',
        'oro/loading-mask',
        'pim/product-manager',
        'pim/fetcher-registry',
        'pimee/published-product-manager',
        'routing',
        'pim/dialog'
    ],
    function (
        $,
        _,
        mediator,
        BaseForm,
        template,
        Navigation,
        LoadingMask,
        ProductManager,
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
                this.listenTo(mediator, 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var categories = this.getFormData().categories;
                var isOwner = this.getFormData().meta.is_owner;

                if (!isOwner) {
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
                    _.bind(this.doPublish, this)
                );
            },
            unpublish: function () {
                Dialog.confirm(
                    _.__('pimee_enrich.entity.product.confirmation.unpublish.content'),
                    _.__('pimee_enrich.entity.product.confirmation.unpublish.title'),
                    _.bind(this.doUnpublish, this)
                );
            },
            doPublish: function () {
                this.togglePublished(true);
            },
            doUnpublish: function () {
                this.togglePublished(false);
            },
            togglePublished: function (publish) {
                var productId   = this.getFormData().meta.id;
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el).show();
                var navigation = Navigation.getInstance();

                var method = publish ? PublishedProductManager.publish : PublishedProductManager.unpublish;
                // TODO: We shouldn't force product fetching, we should use request response (cf. send for approval)
                method(productId)
                    .done(_.bind(function () {
                        ProductManager.get(this.getFormData().meta.id).done(_.bind(function (product) {
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

                            mediator.trigger('pim_enrich:form:entity:post_fetch', product);
                            mediator.trigger('pim_enrich:form:entity:post_publish', product);
                        }, this));
                    }, this))
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
            }
        });
    }
);
