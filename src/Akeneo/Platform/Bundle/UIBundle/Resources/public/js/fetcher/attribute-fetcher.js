'use strict';

define(['jquery', 'underscore', 'pim/base-fetcher', 'routing'], function ($, _, BaseFetcher, Routing) {
  return BaseFetcher.extend({
    identifierPromise: null,
    fetchByTypesPromises: [],

    /**
     * Return the identifier attribute
     *
     * @return {Promise}
     */
    getIdentifierAttribute: function () {
      if (null === this.identifierPromise) {
        this.identifierPromise = $.Deferred();

        return this.fetchByTypes([this.options.identifier_type]).then(
          function (attributes) {
            const mainIdentifier = attributes.find(attribute => attribute.is_main_identifier);
            if (typeof mainIdentifier !== 'undefined') {
              this.identifierPromise.resolve(mainIdentifier).promise();

              return this.identifierPromise;
            }

            return this.identifierPromise.reject().promise();
          }.bind(this)
        );
      }

      return this.identifierPromise;
    },

    /**
     * Fetch attributes by types
     *
     * @param {Array} attributeTypes
     *
     * @return {Promise}
     */
    fetchByTypes: function (attributeTypes, useCache = true, options = {}) {
      var cacheKey = attributeTypes.sort().join('');

      if (!_.has(this.fetchByTypesPromises, cacheKey) || !useCache) {
        this.fetchByTypesPromises[cacheKey] = this.getJSON(this.options.urls.list, {
          types: attributeTypes.join(','),
          options,
        })
          .then(_.identity)
          .promise();
      }

      return this.fetchByTypesPromises[cacheKey];
    },

    search: function (searchOptions) {
      const url = Routing.generate(this.options.urls.list);

      return $.ajax({
        data: JSON.stringify(searchOptions),
        contentType: 'application/json',
        type: 'POST',
        url: url,
      })
        .then(_.identity)
        .promise();
    },

    /**
     * This method overrides the base method, to send a POST query instead of a GET query, because the request
     * URI can be too long.
     * TODO Should be deleted to set it back to GET.
     *
     * {@inheritdoc}
     */
    getJSON: function (url, parameters) {
      return $.post(Routing.generate(url), parameters, null, 'json');
    },

    /**
     * {@inheritdoc}
     */
    clear: function () {
      BaseFetcher.prototype.clear.apply(this, arguments);

      this.fetchByTypesPromises = [];
      this.identifierPromise = null;
    },
  });
});
