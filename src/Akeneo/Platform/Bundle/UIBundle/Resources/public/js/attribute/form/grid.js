'use strict';

define(['pim/form/common/index/grid', 'pim/common/grid', 'pim/user-context'], function (BaseForm, Grid, UserContext) {
  return BaseForm.extend({
    configure: function () {
      BaseForm.prototype.configure.apply(this, arguments);

      var metaData = this.config.metadata || {};
      // Keep the catalog locale context for the queries used to provide the grid data source
      metaData.localeCode = UserContext.get('catalogLocale');

      // Keep the catalog locale context when the user navigates from the product Edit Form
      metaData.dataLocale = UserContext.get('catalogLocale');

      this.grid = new Grid(this.config.alias, metaData);
    },
  });
});
