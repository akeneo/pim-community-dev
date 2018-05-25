define(
    ['jquery', 'underscore', 'backbone', 'oro/translator'],
    function ($, _, Backbone, __) {
        'use strict';

        return Backbone.View.extend({
            el: '.asset-uploader',
            filePrompt: _.template(
                '<img src="/bundles/pimui/images/upload.png" alt="upload icon"><span><%= message %></span>'
            ),
            fileInfo: _.template(
                '<span><%= message %>&nbsp;<i class="icon icon-trash"></i></span>'
            ),
            events: {
                'change input[type=file]': 'onFileChanged',
                'click .icon-trash': 'resetFile'
            },

            /**
             * Change uploader view when changed
             * @param event
             */
            onFileChanged: function (event) {
                event.preventDefault();
                var file = event.currentTarget;
                this.setFileInformations(file);
            },

            /**
             * Change uploader view when changed
             *
             * @param {JQuery} file
             */
            setFileInformations: function (file) {
                var $inputContainer = this.getUploaderContainer(file);
                var $label = $inputContainer.find('.AknMediaField-emptyContainer');
                if (file.value) {
                    var basename = this.basename(file.value);
                    $label.empty();
                    $label.append(this.fileInfo({message: basename}));
                } else {
                    $label.empty();
                    $label.append(this.filePrompt({message: __('pim_enrich.entity.product.media.upload')}));
                }
            },

            /**
             * Reset a file input
             *
             * @param event
             */
            resetFile: function (event) {
                event.stopPropagation();
                var $inputContainer = this.getUploaderContainer(event.currentTarget);
                var file = $inputContainer.find('input');
                file.val('');
                this.setFileInformations(file);
            },

            /**
             * Find the global container of an element
             */
            getUploaderContainer: function (childElement) {
                return $(childElement).closest('.asset-uploader');
            },

            /**
             * Return a file basename
             *
             * @param path
             * @returns {string}
             */
            basename: function (path) {
                return path.replace(/\\/g, '/').replace(/.*\//, '');
            }
        });
    }
);
