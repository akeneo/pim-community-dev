'use strict';

define(['jquery', 'routing', 'oro/mediator', 'pim/cache-invalidator'], function(
  $,
  Routing,
  mediator,
  CacheInvalidator
) {
  return {
    /**
     * Get a published product
     *
     *  @param {string|int} id
     *
     * @return {Promise}
     */
    get: function(id) {
      return $.getJSON(Routing.generate(__moduleConfig.urls.get, {id: id}))
        .then(function(product) {
          var cacheInvalidator = new CacheInvalidator();
          cacheInvalidator.checkStructureVersion(product);

          mediator.trigger('pim_enrich:form:product:post_fetch', product);

          return product;
        })
        .promise();
    },

    /**
     * Publish a product
     *
     * @param  {string} uuid
     *
     * @return {Promise}
     */
    publish: function(uuid) {
      return $.ajax({
        type: 'PUT',
        url: Routing.generate('pimee_workflow_published_product_rest_publish', {originalUuid: uuid}),
        headers: {accept: 'application/json'},
      }).promise();
    },

    /**
     * Unpublish a product
     *
     * @param string uuid
     *
     * @return {Promise}
     */
    unpublish: function(uuid) {
      return $.ajax({
        type: 'DELETE',
        url: Routing.generate('pimee_workflow_published_product_rest_unpublish', {originalUuid: uuid}),
        headers: {accept: 'application/json'},
      }).promise();
    },
  };
});
