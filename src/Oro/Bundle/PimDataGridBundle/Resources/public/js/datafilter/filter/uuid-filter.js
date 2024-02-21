/* global define */
define(['underscore', 'oro/translator', 'oro/datafilter/choice-filter'], function (_, __, ChoiceFilter) {
  'use strict';

  /**
   * @export  oro/datafilter/uuidFilter
   * @class   oro.datafilter.uuidFilter
   * @extends oro.datafilter.ChoiceFilter
   */
  return ChoiceFilter.extend({
    initialize: function () {
      this.choices = [{label: __('pim_datagrid.filters.common.in_list'), value: 'in'}];
      this.emptyValue = {type: 'in', value: ''};

      ChoiceFilter.prototype.initialize.apply(this, arguments);
    },

    /**
     * {@inheritdoc}
     */
    _getOperatorChoices() {
      return {
        in: __('pim_datagrid.filters.common.in_list'),
      };
    },
  });
});
