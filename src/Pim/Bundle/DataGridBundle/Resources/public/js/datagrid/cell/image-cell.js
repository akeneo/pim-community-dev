/* global define */
define(['oro/datagrid/string-cell', 'pim/media-url-generator'],
    function(StringCell, MediaUrlGenerator) {
        'use strict';

        /**
         * Image column cell
         *
         * @extends oro.datagrid.StringCell
         */
        return StringCell.extend({
            /**
             * Render an image.
             */
            render: function () {
                var image = this.formatter.fromRaw(this.model.get(this.column.get("name")));

                var src = MediaUrlGenerator.getMediaShowUrl(image.filePath, 'thumbnail_small');
                this.$el.empty().html('<img src="' + src + '" title="' + image.originalFilename + '" />');

                return this;
            }
        });
    }
);
