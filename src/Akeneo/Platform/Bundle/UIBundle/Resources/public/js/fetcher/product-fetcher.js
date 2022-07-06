'use strict';

define(['jquery', 'backbone', 'pim/base-fetcher', 'routing', 'oro/mediator', 'pim/cache-invalidator'], function (
  $,
  Backbone,
  BaseFetcher,
  Routing,
  mediator,
  CacheInvalidator
) {
  return BaseFetcher.extend({
    /**
     * Fetch a product or a product_model based on its id or uuid
     *
     * @param {string} idOrUuid
     *
     * @return {Promise}
     */
    fetch: function (idOrUuid, options = {}) {
      const {silent = false, ...routeParams} = options;

      const params = (idOrUuid + '').match(/^\d+$/) ? {id: idOrUuid} : {uuid: idOrUuid};

      return $.getJSON(Routing.generate(this.options.urls.get, {...routeParams, ...params}))
        .then(function (product) {
          const cacheInvalidator = new CacheInvalidator();
          cacheInvalidator.checkStructureVersion(product);

          if (!silent) {
            mediator.trigger('pim_enrich:form:product:post_fetch', product);
          }

          return product;
        })
        .promise();
    },

    /**
     * {@inheritdoc}
     */
    getIdentifierField: function () {
      return $.Deferred().resolve('identifier');
    },
  });
});
