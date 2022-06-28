'use strict';

define(['jquery', 'underscore', 'routing', 'pim/base-fetcher'], function($, _, Routing, BaseFetcher) {
  return BaseFetcher.extend({
    fetchAllByUuid: function(uuid) {
      return $.getJSON(Routing.generate(this.options.urls.product_index, {productUuid: uuid})).promise();
    },
  });
});
