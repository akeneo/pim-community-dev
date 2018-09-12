/* global define */
define(['underscore', 'oro/translator', 'oro/datafilter/choice-filter'],
    function (_, __, ChoiceFilter) {
        'use strict';

        /**
         * @export  oro/datafilter/parentFilter
         * @class   oro.datafilter.parentFilter
         * @extends oro.datafilter.ChoiceFilter
         */
        return ChoiceFilter.extend({
            initialize: function() {
                this.choices = [
                    {'label': __('pim_datagrid.filters.common.in_list'), 'value': 'in'},
                    {'label': __('pim_datagrid.filters.common.empty'), 'value': 'empty'},
                ];
                this.emptyValue = { 'type': 'in', 'value': ''};

                ChoiceFilter.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            _getOperatorChoices() {
                return {
                    'in': __('pim_datagrid.filters.common.in_list'),
                    'empty': __('pim_datagrid.filters.common.empty'),
                };
            },

            /**
             * {@inheritDoc}
             */
            _showCriteria() {
                ChoiceFilter.prototype._showCriteria.apply(this, arguments);
                const operator = this._readDOMValue()['type'];

                if (operator === 'in') {
                    this._enableListSelection();
                } else {
                    this._disableListSelection();
                }
                this._focusCriteria();
            },

            /**
             * {@inheritDoc}
             */
            _focusCriteria: function _focusCriteria() {
                this.$(this.criteriaSelector + ' input.select2-input').focus().select();
            },

            /**
             * {@inheritDoc}
             */
            _readDOMValue: function() {
                const operator = this.emptyChoice ? this.$('.active .operator_choice').data('value') : 'in';

                return {
                    value: ['empty', 'not empty'].includes(operator) ? '' : this._getInputValue(this.criteriaValueSelectors.value),
                    type: operator
                };
            },
        });
    });
