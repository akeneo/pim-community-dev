/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(['pim/form/common/edit-form', 'pim/user-context'], function (BaseEditForm, UserContext) {
  return BaseEditForm.extend({
    /**
     * {@inheritdoc}
     */
    configure: function () {
      this.on('pim_enrich:form:entity:post_fetch', this._refreshUserContext);

      return BaseEditForm.prototype.configure.apply(this, arguments);
    },

    _refreshUserContext: function () {
      UserContext.refresh();
    },
  });
});
