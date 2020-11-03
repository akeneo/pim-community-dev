'use strict';

/**
 * Project fetcher.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define(['jquery', 'underscore', 'routing', 'pim/base-fetcher'], function($, _, Routing, BaseFetcher) {
  return BaseFetcher.extend({
    /**
     * Get completeness of a project in terms of a contributor or not.
     *
     * @param {String} identifier
     * @param {String} contributor
     *
     * @return {Promise}
     */
    getCompleteness: function(identifier, contributor) {
      if (_.isUndefined(contributor)) {
        contributor = null;
      }

      return this.getJSON(this.options.urls.completeness, {
        identifier: identifier,
        contributor: contributor,
      });
    },
  });
});
