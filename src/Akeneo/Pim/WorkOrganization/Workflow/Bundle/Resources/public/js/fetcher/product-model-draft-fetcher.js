'use strict';

define(['jquery', 'underscore', 'routing', 'pim/base-fetcher'], function($, _, Routing, BaseFetcher) {
  return BaseFetcher.extend({
    fetchAllById: function(id) {
      return $.getJSON(Routing.generate(this.options.urls.product_model_index, {productModelId: id})).promise();
    },
  });
});
