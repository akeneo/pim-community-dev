define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator'
    ],
    function ($, _, Backbone, t) {
        'use strict';

        var filePrompt = '<span><%= message %></span>';
        var fileInfo   = '<span><%= message %>&nbsp;<i class="icon icon-trash"></i></span>';

        return Backbone.View.extend({
            el: '.asset-uploader',

            events: {
                "change input[type=file]": 'onFileChanged',
                "click .icon-trash": 'resetFile'
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
             * @param event
             */
            setFileInformations: function (file) {
                var $inputContainer = this.getUploaderContainer(file);
                var $label = $inputContainer.find('.uploader span');
                if (file.value) {
                    var basename = this.basename(file.value);
                    $label.replaceWith(_.template(fileInfo, {message: basename}));
                } else {
                    $label.replaceWith(_.template(filePrompt, {message: t('pim_enrich.entity.product.media.upload')}));
                    $inputContainer.find('.uploader').find('i').remove();
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
                console.log(file.val());
                file.val('');
                this.setFileInformations(file);
                console.log(file.val());
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
