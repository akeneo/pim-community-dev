'use strict';

define(['jquery', 'pim/form', 'pim/router'], function($, BaseForm, router) {
  return BaseForm.extend({
    initialize: function(options) {
      options = options || {};

      this.config = options.config;
    },
    render: function() {
      let jobInstance = $.extend(true, {}, this.getFormData());

      router.redirectToRoute(this.config.redirectPath, {code: jobInstance.code});
    },
  });
});
