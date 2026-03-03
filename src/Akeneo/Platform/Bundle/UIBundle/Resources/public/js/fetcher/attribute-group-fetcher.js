'use strict';

/**
 * Attribute group fetcher
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(['jquery', 'underscore', 'pim/base-fetcher', 'routing'], function ($, _, BaseFetcher, Routing) {
  return BaseFetcher.extend({
    /**
     * Overrides base method, to send query using POST instead GET,
     * because the request URI can be too long.
     * TODO Should be deleted to set it back to GET.
     * SEE attribute fetcher
     *
     * {@inheritdoc}
     */
    getJSON: function (url, parameters) {
      return $.post(Routing.generate(url), parameters, null, 'json');
    },

    /**
     * Overrides bas method to remove the limit and fetch all the attribute groups.
     *
     * {@inheritdoc}
     */
    fetchAll: function (options) {
      options = options || {};

      if (null === this.entityListPromise || false === options.cached) {
        if (!_.has(this.options.urls, 'list')) {
          return $.Deferred().reject().promise();
        }

        this.entityListPromise = $.getJSON(Routing.generate(this.options.urls.list)).then(_.identity).promise();
      }

      return this.entityListPromise;
    },

    /**
     * Fetch all entities for the given identifiers
     *
     * @param {Array} identifiers
     *
     * @return {Promise}
     */
    fetchByIdentifiers: function (identifiers, options) {
      options = options || {};

      if (0 === identifiers.length) {
        return $.Deferred().resolve([]).promise();
      }

      const uncachedIdentifiers = _.difference(identifiers, _.keys(this.entityPromises));
      if (0 === uncachedIdentifiers.length) {
        return this.getObjects(_.pick(this.entityPromises, identifiers));
      }

      options.options = options.options || {};
      options.options.limit = uncachedIdentifiers.length;

      return $.when(
        this.getJSON(this.options.urls.list, _.extend({identifiers: uncachedIdentifiers.join(',')}, options)).then(
          _.identity
        ),
        this.getIdentifierField()
      ).then(
        function (entities, identifierCode) {
          _.each(
            entities,
            function (entity) {
              this.entityPromises[entity[identifierCode]] = $.Deferred().resolve(entity).promise();
            }.bind(this)
          );

          return this.getObjects(_.pick(this.entityPromises, identifiers));
        }.bind(this)
      );
    },
  });
});
