define(
    ['jquery', 'underscore', 'backbone', 'routing'],
    function ($, _, Backbone, Routing) {
        'use strict';

        return {
            /**
             * @return {Object}
             */
            fetch: function (update_url) {
                $.getJSON(update_url)
                    .done(function(versionData) {
                        // TODO we could return the version and update the field directly from the twig
                        var lastPatch = _.first(versionData);
                        $('.last-patch-available:first').append(lastPatch.name);
                    })
                    // TODO: how to properly handle this?
                    .fail(function( jqxhr, textStatus, error ) {
                        var err = textStatus + ", " + error;
                        console.log( "Request Failed: " + err );
                    });
                    // TODO: how to properly handle 404 and CORS?
            }
        };
    }
);
