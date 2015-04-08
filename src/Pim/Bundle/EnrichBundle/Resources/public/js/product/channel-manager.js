'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/config-manager'
    ],
    function (
        $,
        _,
        ConfigManager
    ) {
    return {
        locales: null,
        getChannels: function () {
            var promise = $.Deferred();

            ConfigManager.getEntityList('channels').done(function(channels) {
                promise.resolve(channels);
            });

            return promise.promise();
        },
        getLocales: function() {
            var promise = $.Deferred();

            ConfigManager.getEntityList('channels').done(function(channels) {
                var locales = _.unique(_.flatten(_.pluck(channels, 'locales')));
                promise.resolve(locales);
            });

            return promise.promise();
        }
    };
});
