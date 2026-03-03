'use strict';

define(['underscore', 'pim/remover/base', 'routing'], function (_, BaseRemover, Routing) {
  return _.extend({}, BaseRemover, {
    /**
     * {@inheritdoc}
     */
    getUrl: function (uuid) {
      return Routing.generate(__moduleConfig.url, {uuid});
    },
  });
});
