/* global define */
define(['underscore', 'oro/translator', 'oro/datafilter/choice-filter'],
  function (_, __, ChoiceFilter) {
    'use strict';

    /**
     * @export  oro/datafilter/identifierFilter
     * @class   oro.datafilter.identifierFilter
     * @extends oro.datafilter.ChoiceFilter
     */
    return ChoiceFilter.extend({
      initialize: function() {
        this.choices = [
          {'label': __('pim.grid.choice_filter.label_contains'), 'value': '1'},
          {'label': __('pim.grid.choice_filter.label_does_not_contain'), 'value': '2'},
          {'label': __('pim.grid.choice_filter.label_equal'), 'value': '3'},
          {'label': __('pim.grid.choice_filter.label_start_with'), 'value': '4'},
          {'label': __('pim.grid.choice_filter.label_in_list'), 'value': 'in'},
        ];
        this.emptyValue = { 'type': 'in', 'value': ''};

        ChoiceFilter.prototype.initialize.apply(this, arguments);
      },

      /**
       * {@inheritdoc}
       */
      _getOperatorChoices() {
        return {
          '1': __('pim.grid.choice_filter.label_contains'),
          '2': __('pim.grid.choice_filter.label_does_not_contain'),
          '3': __('pim.grid.choice_filter.label_equal'),
          '4': __('pim.grid.choice_filter.label_start_with'),
          'in': __('pim.grid.choice_filter.label_in_list'),
        };
      },
    });
  });
