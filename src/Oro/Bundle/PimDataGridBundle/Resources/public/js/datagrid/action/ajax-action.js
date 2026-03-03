/* global define */
define(['oro/datagrid/model-action'], function (ModelAction) {
  'use strict';

  /**
   * Ajax action, triggers REST AJAX request
   *
   * @export  oro/datagrid/ajax-action
   * @class   oro.datagrid.AjaxAction
   * @extends oro.datagrid.ModelAction
   */
  return ModelAction.extend({
    /** @property {Boolean} */
    noHref: false,

    /**
     * Creates launcher
     *
     * @param {Object} options Launcher options
     * @return {oro.datagrid.ActionLauncher}
     */
    createLauncher: function (options) {
      this.launcherOptions = _.extend({noHref: this.noHref}, this.launcherOptions);

      return ModelAction.prototype.createLauncher.apply(this, arguments);
    },
  });
});
