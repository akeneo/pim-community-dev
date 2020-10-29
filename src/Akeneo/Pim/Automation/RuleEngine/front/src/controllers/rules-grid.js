/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define(['underscore', 'pim/form', 'pim/common/grid'], function(_, BaseForm, Grid) {
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
        this.grid = new Grid('rule-grid', {
          resourceName: this.config.resourceName,
          resourceId: this.getFormData().meta.id,
        });
      }

      this.$el.empty().append(this.grid.render().$el);
    },
  });
});
