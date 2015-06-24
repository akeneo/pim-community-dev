define(
    [
        'jquery',
        'underscore',
        'backbone',
        'oro/translator'
    ],
    function ($, _, Backbone, t) {
        'use strict';

        return Backbone.View.extend({
            el: '.media-uploader',

            events: {
                "change input[type=file]": 'onFileChanged'
            },

            /**
             * Change uploader view when changed
             * @param event
             */
            onFileChanged: function (event) {
                event.preventDefault();
                var file = event.currentTarget;
                var basename = this.basename(file.value);
                var $inputContainer = this.getUploaderContainer(file);
                var $label = $inputContainer.find('div.uploader span');
                $label.text(basename);
                var $iconContainer = $inputContainer.find('div.icons-container');
                $iconContainer.append('<i class="icon icon-trash"></i>');
                $iconContainer.css('opacity', 'none');
            },

            /**
             * Find the container of changed input file
             */
            getUploaderContainer: function (fileInput) {
                return $(fileInput).closest('.media-uploader');
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
