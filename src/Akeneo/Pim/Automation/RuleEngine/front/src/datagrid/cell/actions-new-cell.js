/* global define */

define([
    'oro/datagrid/string-cell'
], function(StringCell) {
  'use strict';
  return StringCell.extend({
    render: function () {
      const content = this.model.get(this.column.get('content'));

      this.$el.text(JSON.stringify(content));
      return this;
    },
  });
});
