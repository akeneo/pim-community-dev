/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(['underscore', 'pim/form', 'pim/common/grid', 'pim/user-context'], function(_, BaseForm, Grid, UserContext) {
  return BaseForm.extend({
    grid: null,

    /**
     * @param {Object} meta
     */
    initialize: function(meta) {
      this.config = _.extend({}, meta.config);
    },

    /**
     * {@inheritdoc}
     */
    render: function() {
      if (!this.grid) {
        this.grid = new Grid('attribute-rule-grid', {
          resourceName: this.config.resourceName,
          resourceId: this.getFormData().meta.id,
          localeCode: UserContext.get('catalogLocale')
        });
      }

      this.$el.empty().append(this.grid.render().$el);
    },
  });
});
