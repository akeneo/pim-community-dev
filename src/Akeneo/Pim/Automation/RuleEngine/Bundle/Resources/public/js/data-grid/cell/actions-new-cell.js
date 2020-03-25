/* global define */

define([], function() {
  'use strict';
  return {
    render: function () {
      this.$el.text("toto");
      return this;
    },
  };
});
