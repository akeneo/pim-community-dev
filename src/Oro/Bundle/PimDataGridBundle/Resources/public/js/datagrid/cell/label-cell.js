/* global define */
define(['oro/datagrid/string-cell'], function (StringCell) {
  'use strict';

  /**
   * Label column cell
   *
   * @extends oro.datagrid.StringCell
   */
  return StringCell.extend({
    className: 'AknGrid-bodyCell AknGrid-bodyCell--noWrap AknGrid-bodyCell--highlight',
    render: function () {
      StringCell.prototype.render.apply(this, arguments);
      this.$el.prop('title', this.$el.text());

      return this;
    },
  });
});
