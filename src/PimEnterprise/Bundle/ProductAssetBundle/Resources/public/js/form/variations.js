define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/delete-confirmation',
        'oro/translator',
        'oro/navigation'
    ],
    function ($, _, Backbone, DeleteConfirmation, t, Navigation) {
        'use strict';

        var AssetVariations = Backbone.View.extend({
            initialize: function() {
                this.$el = $("#pimee_product_asset-tabs-variations");
            },
            events: {
                "click .delete": "confirmDelete",
                "click .reset-variations": "confirmResetVariations",
                "change input[type=file]": "fileReadyForUpload"
            },
            confirmDelete: function (event) {
                event.preventDefault();
                var button = event.currentTarget;
                var targetUrl = $(button).data('href');
                var confirmation = this.getConfirmDialog('Are you sure you want to delete this item ?', targetUrl);
                confirmation.open();
            },
            confirmResetVariations: function (event) {
                event.preventDefault();
                var button = event.currentTarget;
                var targetUrl = $(button).data('href');
                var confirmation = this.getConfirmDialog('Are you sure you want to reset all variations ?', targetUrl, 'reset.variations');
                confirmation.open();
            },
            fileReadyForUpload: function (event) {
                event.preventDefault();
                var file = event.currentTarget;
                var container = $(file).parent();
                console.log(this.basename(file.value));
                console.log(container);
            },
            getConfirmDialog: function (message, targetUrl, title) {
                var options = {
                    content: t(message)
                };
                if (title) {
                    options.title = t(title);
                }
                var confirmModal = new DeleteConfirmation(options);
                confirmModal.on('ok', function () {
                    Navigation.getInstance().setLocation(targetUrl);
                });
                return confirmModal;
            },
            basename: function (path) {
                return path.replace(/\\/g, '/').replace(/.*\//, '');
            }
        });

        return AssetVariations;
    }
);
