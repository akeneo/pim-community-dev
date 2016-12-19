'use strict';

/**
 * Project fetcher.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(
    ['jquery', 'underscore', 'routing', 'pim/base-fetcher'],
    function ($, _, Routing, BaseFetcher) {
        return BaseFetcher.extend({
            /**
             * Get completeness of a project in terms of a contributor or not.
             *
             * @param {String} projectCode
             * @param {String} username
             *
             * @returns {Promise}
             */
            getCompleteness: function (projectCode, username) {
                if (_.isUndefined(username) || 'string' !== typeof username) {
                    username = null;
                }
                var deferred = $.Deferred();
                var todo = Math.floor((Math.random() * 100) + 1);
                var inProgress = Math.floor((Math.random() * 100) + 1);
                var done = Math.floor((Math.random() * 100) + 1);

                deferred.resolve({todo: todo, in_progress: inProgress, done: done});

                return deferred;
            }
        });
    }
);
