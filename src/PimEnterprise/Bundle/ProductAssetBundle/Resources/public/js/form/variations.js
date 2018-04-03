define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/delete-confirmation',
        'oro/translator',
        'pim/router',
        'pimee/productasset/uploader'
    ],
    function ($, _, Backbone, DeleteConfirmation, __, router, Uploader) {
        'use strict';

        return Backbone.View.extend({
            el: '#pimee_product_asset-tabs-variations',
            uploader: undefined,
            events: {
                'click .delete': 'confirmDelete',
                'click .reset-variations': 'confirmResetVariations'
            },
            initialize: function () {
                this.uploader = new Uploader();
            },
            confirmDelete: function (event) {
                event.preventDefault();
                var button = event.currentTarget;
                var targetUrl = $(button).data('href');
                var confirmation = this.getConfirmDialog(
                    'pimee_product_asset.enrich_variation.popin.delete.message',
                    targetUrl,
                    'pim_common.confirm_deletion'
                );
                confirmation.open();
            },
            confirmResetVariations: function (event) {
                event.preventDefault();
                var button = event.currentTarget;
                var targetUrl = $(button).data('href');
                var confirmation = this.getConfirmDialog(
                    'pimee_product_asset.enrich_variation.popin.reset.message',
                    targetUrl,
                    'pimee_product_asset.enrich_variation.popin.reset.title'
                );
                confirmation.open();
            },
            getConfirmDialog: function (message, targetUrl, title) {
                var options = {
                    content: __(message)
                };
                if (title) {
                    options.title = __(title);
                }
                var confirmModal = new DeleteConfirmation(options);
                confirmModal.on('ok', function () {
                    router.redirect(targetUrl);
                });

                return confirmModal;
            }
        });
    }
);
