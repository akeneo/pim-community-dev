'use strict';

define(
    ['jquery', 'underscore', 'pim/entity-manager'],
    function ($, _, EntityManager) {
        return {
            locales: null,
            getChannels: function () {
                var promise = $.Deferred();

                EntityManager.getEntityList('channels').done(function (channels) {
                    promise.resolve(channels);
                });

                return promise.promise();
            },
            getLocales: function () {
                var promise = $.Deferred();

                EntityManager.getEntityList('channels').done(function (channels) {
                    var locales = _.unique(_.flatten(_.pluck(channels, 'locales')));
                    promise.resolve(locales);
                });

                return promise.promise();
            }
        };
    }
);
