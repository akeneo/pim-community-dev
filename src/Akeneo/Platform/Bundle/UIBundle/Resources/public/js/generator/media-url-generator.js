'use strict';

define([
        'jquery',
        'underscore',
        'routing'
    ], function (
        $,
        _,
        Routing
    ) {
        return {
            /**
             * Get the show media URL
             *
             * @param string filePath
             * @param string filter
             *
             * @return {string}
             */
            getMediaShowUrl: function (filePath, filter) {
                var filename = encodeURIComponent(filePath);

                // In case the filepath is already a direct URL to an asset preview, directly returns it
                if (filePath && filePath.includes('rest/asset_manager/image_preview')) {
                    return filePath;
                }

                return Routing.generate('pim_enrich_media_show', {
                    filename: filename,
                    filter: filter
                });
            },

            /**
             * Get the download media URL
             *
             * @param string filePath
             *
             * @return {string}
             */
            getMediaDownloadUrl: function (filePath) {
                var filename = encodeURIComponent(filePath);

                return Routing.generate('pim_enrich_media_download', {
                    filename: filename
                });
            }
        };
    }
);
