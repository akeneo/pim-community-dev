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
        'pimee/permission-manager',
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
        PermissionManager,
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
                mediator.on('product:action:post_update', _.bind(this.render, this));

                return $.when(
                    PermissionManager.getPermissions().then(_.bind(function (permissions) {
                        this.permissions = permissions;
                    }, this)),
                    BaseForm.prototype.configure.apply(this, arguments)
                );
            },
            render: function () {
                var categories = this.getFormData().categories;
                var isOwner = !categories.length ||
                    !!_.intersection(this.permissions.categories.OWN_PRODUCTS, categories).length;

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
                method(productId)
                    .done(_.bind(function () {
                        ProductManager.clear(this.getFormData().meta.id);
                        ProductManager.get(this.getFormData().meta.id).done(_.bind(function (product) {
                            this.setData(product);
                            navigation.addFlashMessage(
                                'success',
                                _.__(
                                    'pimee_enrich.entity.product.flash.product_' +
                                    (publish ? 'published' : 'unpublished')
                                )
                            );
                            navigation.afterRequest();

                            loadingMask.hide().$el.remove();
                            this.render();
                            mediator.trigger('product:action:post_publish', product);
                            mediator.trigger('product:action:post_update', product);
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
