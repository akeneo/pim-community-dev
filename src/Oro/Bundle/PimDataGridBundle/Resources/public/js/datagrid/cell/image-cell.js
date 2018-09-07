/* global define */
define(
    [
        'underscore',
        'oro/datagrid/string-cell',
        'pim/media-url-generator',
        'pim/template/datagrid/cell/image-cell'
    ],
    function (
        _,
        StringCell,
        MediaUrlGenerator,
        template
    ) {
        'use strict';

        /**
         * Image column cell
         *
         * @export  oro/datagrid/image-cell
         * @class   oro.datagrid.ImageCell
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            template: _.template(template),

            /**
             * Render an image.
             */
            render: function () {
                const image = this.formatter.fromRaw(this.model.get(this.column.get("name")));

                const src = MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail_small');
                this.$el.empty().html(this.getTemplate({label: image.originalFilename, src}));

                return this;
            },

            /**
             * Returns the template used to show the image.
             *
             * This function can be overridden to alter the way the image is shown.
             *
             * @returns {string}
             */
            getTemplate(params) {
                return this.template(params);
            }
        });
    }
);
