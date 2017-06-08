define(
    [
        'jquery',
        'underscore',
        'oro/datafilter/number-filter',
        'oro/app',
        'pim/template/datagrid/filter/metric-filter'
    ],
    function ($, _, NumberFilter, app, template) {
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
                if (_.contains(['empty', 'not empty'], value.type)) {
                    return this._getChoiceOption(value.type).label;
                }
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
            popupCriteriaTemplate: _.template(template),

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
             * @property {Object}
             */
            emptyValue: {
                unit:  '',
                type:  '',
                value: ''
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
                if (_.contains(['empty', 'not empty'], newValue.type)) {
                    this.$(this.criteriaValueSelectors.value).hide().siblings('.btn-group:eq(1)').hide();
                } else {
                    this.$(this.criteriaValueSelectors.value).show().siblings('.btn-group:eq(1)').show();
                }

                this._triggerUpdate(newValue, oldValue);
                this._updateCriteriaHint();
            },

            /**
             * @inheritDoc
             */
            setValue: function(value) {
                value = this._formatRawValue(value);
                if (this._isNewValueUpdated(value)) {
                    var oldValue = this.value;
                    this.value = app.deepClone(value);
                    this._updateDOMValue();
                    this._onValueUpdated(this.value, oldValue);
                }

                return this;
            },

            /**
             * @inheritDoc
             */
            _onClickChoiceValue: function(e) {
                NumberFilter.prototype._onClickChoiceValue.apply(this, arguments);
                var parentDiv = $(e.currentTarget).closest('.metricfilter');
                if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                    parentDiv.find('input[name="value"], .btn-group:eq(1)').hide();
                } else {
                    parentDiv.find('input[name="value"], .btn-group:eq(1)').show();
                }
            },

            /**
             * @inheritDoc
             */
            reset: function() {
                this.setValue(this.emptyValue);
                this.trigger('update');

                return this;
            }
        });
    }
);
