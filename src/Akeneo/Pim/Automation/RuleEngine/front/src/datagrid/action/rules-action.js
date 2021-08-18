/* global define */
define(['oro/datagrid/mass-action', 'pim/user-context'], function(MassAction, UserContext) {
  'use strict';

  return MassAction.extend(
    {
      /**
       * {@inheritdoc}
       */
      getActionParameters: function() {
        let massActionParam = MassAction.prototype.getActionParameters.apply(this, arguments);

        return { dataLocale: UserContext.get('catalogLocale'), ...massActionParam };
      },
    }
  );
});
