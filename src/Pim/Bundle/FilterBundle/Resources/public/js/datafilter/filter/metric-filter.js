define(
    ['jquery', 'underscore', 'oro/datafilter/number-filter', 'oro/app'],
    function ($, _, NumberFilter, app) {
        'use strict';

        /**
         * Metric filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  pim/datafilter/metric-filter
         * @class   pim.datafilter.MetricFilter
         * @extends oro.datafilter.NumberFilter
         */
        return NumberFilter.extend({
            /**
             * @inheritDoc
             */
            _renderCriteria: function (el) {
                $(el).append(this.popupCriteriaTemplate({
                    name: this.name,
                    choices: this.choices,
                    units: this.units
                }));

                return this;
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function (value) {
                this._setInputValue(this.criteriaValueSelectors.value, value.value);
                this._setInputValue(this.criteriaValueSelectors.type, value.type);
                this._setInputValue(this.criteriaValueSelectors.unit, value.unit);

                return this;
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function () {
                return {
                    value: this._getInputValue(this.criteriaValueSelectors.value),
                    type: this._getInputValue(this.criteriaValueSelectors.type),
                    unit: this._getInputValue(this.criteriaValueSelectors.unit)
                };
            },

            /**
             * @inheritDoc
             */
            _getCriteriaHint: function () {
                var value = this._getDisplayValue();
                if (!value.value) {
                    return this.defaultCriteriaHint;
                } else if (_.has(this.choices, value.type) && _.has(this.units, value.unit)) {
                    return this.choices[value.type] + ' ' + value.value + ' ' + value.unit;
                } else if (_.has(this.choices, value.type)) {
                    return this.choices[value.type] + ' ' + value.value;
                } else {
                    return value.value;
                }
            },

            /**
             * @inheritDoc
             */
            popupCriteriaTemplate: _.template(
                '<div class="metricfilter choicefilter">' +
                    '<div class="input-prepend input-append">' +
                        '<div class="btn-group">' +
                            '<button class="btn dropdown-toggle" data-toggle="dropdown">' +
                                'Action' +
                                '<span class="caret"></span>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<% _.each(choices, function (hint, value) { %>' +
                                    '<li><a class="choice_value" href="#" data-value="<%= value %>"><%= hint %></a></li>' +
                                '<% }); %>' +
                            '</ul>' +
                            '<input class="name_input" type="hidden" name="metric_type" id="<%= name %>" value=""/>' +
                        '</div>' +

                        '<input type="text" name="value" value="">' +

                        '<div class="btn-group">' +
                            '<button class="btn dropdown-toggle" data-toggle="dropdown">' +
                                'Unit' +
                                '<span class="caret"></span>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<% _.each(units, function (hint, value) { %>' +
                                    '<li><a class="choice_value" href="#" data-value="<%= value %>"><%= _.__(hint) %></a></li>' +
                                '<% }); %>' +
                            '</ul>' +
                            '<input class="name_input" type="hidden" name="metric_unit" id="<%= name %>" value=""/>' +
                        '</div>' +
                    '</div>' +
                    '<button class="btn btn-primary filter-update" type="button">Update</button>' +
                '</div>'
            ),

            /**
             * Selectors for filter criteria elements
             *
             * @property {Object}
             */
            criteriaValueSelectors: {
                unit: 'input[name="metric_unit"]',
                type:     'input[name="metric_type"]',
                value:    'input[name="value"]'
            },

            /**
             * Empty value object
             *
             * @property {0bject}
             */
            emptyValue: {
                unit: '',
                type:     '',
                value:    ''
            },

            /**
             * Check if all properties of the value have been specified or all are empty (for reseting filter)
             *
             * @param value
             * @return boolean
             */
            _isValueValid: function(value) {
                return (value.unit && value.type && value.value) ||
                       (!value.unit && !value.type && !value.value);
            },

            /**
             * @inheritDoc
             */
            setValue: function(value) {
                value = this._formatRawValue(value);
                if (this._isValueValid(value)) {
                    if (this._isNewValueUpdated(value)) {
                        var oldValue = this.value;
                        this.value = app.deepClone(value);
                        this._updateDOMValue();
                        this._onValueUpdated(this.value, oldValue);
                    }
                }
                return this;
            }
        });
    }
);
