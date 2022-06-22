'use strict';

define(['underscore', 'pim/base-fetcher'], function (_, BaseFetcher) {
  return BaseFetcher.extend({
    /**
     * Fetch only the parent category tree
     * User right will not be apply.
     * @return {Promise}
     */
    fetchCategoryTree: function () {
      return this.getJSON(this.options.urls.list_channel_category_tree).then(_.identity).promise();
    },
  });
});
