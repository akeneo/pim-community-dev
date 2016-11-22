'use strict';

define(
    ['jquery', 'underscore', 'routing', 'pim/base-fetcher'],
    function ($, _, Routing, BaseFetcher) {
        return BaseFetcher.extend({
            /**
             * Search contributors of a project. It searches in full name.
             *
             * @param {int}   projectCode
             * @param {Array} searchOptions
             *
             * @returns {Promise}
             */
            searchContributors: function (projectCode, searchOptions) {
                var deferred = $.Deferred();

                $.getJSON(
                    Routing.generate(
                        'activity_manager_project_contributors_search',
                        {projectCode: projectCode, search: searchOptions.search, options: searchOptions.options}
                    )
                ).then(function (contributors) {
                    deferred.resolve(contributors);
                });

                return deferred;
            },

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
