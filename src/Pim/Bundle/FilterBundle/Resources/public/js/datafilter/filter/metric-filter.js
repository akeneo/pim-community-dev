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
         * @export  oro/datafilter/metric-filter
         * @class   oro.datafilter.MetricFilter
         * @extends oro.datafilter.NumberFilter
         */
        return NumberFilter.extend({
            /**
             * @inheritDoc
             */
            initialize: function() {
                NumberFilter.prototype.initialize.apply(this, arguments);

                this.on('disable', this._onDisable, this);

            },

            _onDisable: function() {
                this.$('.choicefilter button.dropdown-toggle').first().html(_.__('Action') + '<span class="caret"></span>');
                this.$('.choicefilter button.dropdown-toggle').last().html(_.__('Unit') + '<span class="caret"></span>');
            },

            /**
             * @inheritDoc
             */
            _renderCriteria: function (el) {
                $(el).append(this.popupCriteriaTemplate({
                    name:    this.name,
                    choices: this.choices,
                    units:   this.units
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
                var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();

                if (!value.value) {
                    return this.placeholder;
                } else {
                    var operator = _.find(this.choices, function(choice) {
                        return choice.value == value.type;
                    });
                    operator = operator ? operator.label : '';

                    return operator + ' "' + value.value + ' ' + _.__(value.unit) + '"';
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
                                '<%= _.__("Action") %>' +
                                '<span class="caret"></span>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<% _.each(choices, function (choice) { %>' +
                                    '<li><a class="choice_value" href="#" data-value="<%= choice.value %>"><%= choice.label %></a></li>' +
                                '<% }); %>' +
                            '</ul>' +
                            '<input class="name_input" type="hidden" name="metric_type" value=""/>' +
                        '</div>' +

                        '<input type="text" name="value" value="">' +

                        '<div class="btn-group">' +
                            '<button class="btn dropdown-toggle" data-toggle="dropdown">' +
                                '<%= _.__("Unit") %>' +
                                '<span class="caret"></span>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<% _.each(units, function (code, label) { %>' +
                                    '<li><a class="choice_value" href="#" data-value="<%= code %>"><%= _.__(label) %></a></li>' +
                                '<% }); %>' +
                            '</ul>' +
                            '<input class="name_input" type="hidden" name="metric_unit" value=""/>' +
                        '</div>' +
                    '</div>' +
                    '<button class="btn btn-primary filter-update" type="button"><%= _.__("Update") %></button>' +
                '</div>'
            ),

            /**
             * Selectors for filter criteria elements
             *
             * @property {Object}
             */
            criteriaValueSelectors: {
                unit:  'input[name="metric_unit"]',
                type:  'input[name="metric_type"]',
                value: 'input[name="value"]'
            },

            /**
             * Empty value object
             *
             * @property {0bject}
             */
            emptyValue: {
                unit:  '',
                type:  '',
                value: ''
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
            _triggerUpdate: function(newValue, oldValue) {
                if (!app.isEqualsLoosely(newValue, oldValue)) {
                    this.trigger('update');
                }
            },

            /**
             * @inheritDoc
             */
            _onValueUpdated: function(newValue, oldValue) {
                var menu = this.$('.choicefilter .dropdown-menu');

                menu.find('li a').each(function() {
                    var item = $(this),
                        value = item.data('value');

                    if (item.parent().hasClass('active')) {
                        if (value == newValue.type || value == newValue.unit) {
                            item.parent().removeClass('active');
                        } else {
                        }
                    } else if (value == newValue.type || value == newValue.unit) {
                        item.parent().addClass('active');
                        item.closest('.btn-group').find('button').html(item.html() + '<span class="caret"></span>');
                    }
                });

                this._triggerUpdate(newValue, oldValue);
                this._updateCriteriaHint();
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
