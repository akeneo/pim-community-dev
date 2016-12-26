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
             * @param {String} contributor
             *
             * @returns {Promise}
             */
            getCompleteness: function (projectCode, contributor) {
                if (_.isUndefined(contributor)) {
                    contributor = null;
                }

                return this.getJSON(
                    this.options.urls.completeness,
                    {
                        projectCode: projectCode,
                        contributor: contributor
                    }
                );
            }
        });
    }
);
