define(
    ['jquery', 'underscore', 'backbone', 'routing'],
    function ($, _, Backbone, Routing) {
        'use strict';

        return {
            /**
             * @return {Object}
             */
            fetch: function () {
                $.getJSON(Routing.generate('pim_notification_version_collect_data'))
                    .done(function(updateData) {
                        $.getJSON(updateData.update_url)
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
                    })
                    // TODO: how to properly handle this?
                    .fail(function( jqxhr, textStatus, error ) {
                        var err = textStatus + ", " + error;
                        console.log( "Request Failed: " + err );
                    });
            }
        };
    }
);
