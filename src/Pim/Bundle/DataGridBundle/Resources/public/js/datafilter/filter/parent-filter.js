/* global define */
define(['underscore', 'oro/translator', 'oro/datafilter/choice-filter'],
    function (_, __, ChoiceFilter) {
        'use strict';

        /**
         * Number filter: formats value as a number
         *
         * @export  oro/datafilter/number-filter
         * @class   oro.datafilter.NumberFilter
         * @extends oro.datafilter.ChoiceFilter
         */
        return ChoiceFilter.extend({
            /**
             * Initialize.
             *
             * @param {Object} options
             */
            initialize: function() {
                this.choices = [
                    {'label': __('pim.grid.choice_filter.label_in_list'), 'value': 'in'},
                    {'label': __('pim.grid.choice_filter.label_empty'), 'value': 'empty'},
                ];
                this.emptyValue = { 'type': 'in', 'value': ''};

                ChoiceFilter.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            _getOperatorChoices() {
                return {
                    'in': __('pim.grid.choice_filter.label_in_list'),
                    'empty': __('pim.grid.choice_filter.label_empty'),
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
             * Focus filter criteria input
             *
             * @protected
             */
            _focusCriteria: function _focusCriteria() {
                this.$(this.criteriaSelector + ' input.select2-input').focus().select();
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function() {
                const operator = this.emptyChoice ? this.$('.active .operator_choice').data('value') : 'in';

                return {
                    value: _.contains(['empty', 'not empty'], operator) ? '' : this._getInputValue(this.criteriaValueSelectors.value),
                    type: operator
                };
            },
        });
    });
