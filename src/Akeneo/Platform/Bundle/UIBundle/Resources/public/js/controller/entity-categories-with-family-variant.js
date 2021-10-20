'use strict';

define([
  'underscore',
  'pim/controller/base',
  'pim/router',
  'jquery'
], function (_, BaseController, Router, $) {
  return BaseController.extend({
    active: true,
    config: {},

    /**
     * {@inheritdoc}
     */
    initialize: function (options) {
      this.config = options.config;
    },

    /**
     * {@inheritdoc}
     */
    renderRoute: function (route) {
      sessionStorage.setItem('redirectTab', '#' + this.config.redirectTabName);
      sessionStorage.setItem('current_column_tab', this.config.redirectTabName);

      Router.redirectToRoute(this.config.redirectRouteName, {id : route.params.id});

      return $.Deferred().resolve();
    },
  });
});
