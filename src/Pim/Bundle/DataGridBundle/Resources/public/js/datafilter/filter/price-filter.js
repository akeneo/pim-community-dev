define(
    ['jquery', 'underscore', 'oro/datafilter/number-filter', 'oro/app'],
    function ($, _, NumberFilter, app) {
        'use strict';

        /**
         * Price filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  pim/datafilter/price-filter
         * @class   pim.datafilter.PriceFilter
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
                this.$('.choicefilter button.dropdown-toggle').last().html(_.__('Currency') + '<span class="caret"></span>');
            },

            /**
             * @inheritDoc
             */
            _renderCriteria: function (el) {
                $(el).append(this.popupCriteriaTemplate({
                    name: this.name,
                    choices: this.choices,
                    currencies: this.currencies
                }));

                return this;
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function (value) {
                this._setInputValue(this.criteriaValueSelectors.value, value.value);
                this._setInputValue(this.criteriaValueSelectors.type, value.type);
                this._setInputValue(this.criteriaValueSelectors.currency, value.currency);

                return this;
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function () {
                return {
                    value: this._getInputValue(this.criteriaValueSelectors.value),
                    type: this._getInputValue(this.criteriaValueSelectors.type),
                    currency: this._getInputValue(this.criteriaValueSelectors.currency)
                };
            },

            /**
             * @inheritDoc
             */
            _getCriteriaHint: function () {
                var value = this._getDisplayValue();
                if (value.type === 'empty' && value.currency) {
                    return this._getChoiceOption(value.type).label + ': ' + value.currency;
                }
                if (!value.value) {
                    return this.placeholder;
                } else {
                    var option = this._getChoiceOption(value.type);
                    return option.label + ' ' + value.value + ' ' + value.currency;
                }
            },

            /**
             * @inheritDoc
             */
            popupCriteriaTemplate: _.template(
                '<div class="currencyfilter choicefilter">' +
                    '<div class="input-prepend input-append">' +
                        '<div class="btn-group">' +
                            '<button class="btn dropdown-toggle" data-toggle="dropdown">' +
                                '<%= _.__("Action") %>' +
                                '<span class="caret"></span>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<% _.each(choices, function (option) { %>' +
                                    '<li><a class="choice_value" href="#" data-value="<%= option.value %>" data-input-toggle="true"><%= option.label %></a></li>' +
                                '<% }); %>' +
                            '</ul>' +
                            '<input class="name_input" type="hidden" name="currency_type" value=""/>' +
                        '</div>' +
                        '<input type="text" name="value" value="">' +
                        '<div class="btn-group">' +
                            '<button class="btn dropdown-toggle" data-toggle="dropdown">' +
                                '<%= _.__("Currency") %>' +
                                '<span class="caret"></span>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                                '<% _.each(currencies, function (currency) { %>' +
                                    '<li><a class="choice_value" href="#" data-value="<%= currency %>"><%= currency %></a></li>' +
                                '<% }); %>' +
                            '</ul>' +
                            '<input class="name_input" type="hidden" name="currency_currency" value=""/>' +
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
                currency: 'input[name="currency_currency"]',
                type:     'input[name="currency_type"]',
                value:    'input[name="value"]'
            },

            /**
             * Empty value object
             *
             * @property {0bject}
             */
            emptyValue: {
                currency: '',
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
                return (value.currency && value.type && !_.isUndefined(value.value)) ||
                       (!value.currency && !value.type && _.isUndefined(value.value)) ||
                       (value.type === 'empty' && value.currency);
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
                        if (value == newValue.type || value == newValue.currency) {
                            item.parent().removeClass('active');
                        } else {
                        }
                    } else if (value == newValue.type || value == newValue.currency) {
                        item.parent().addClass('active');
                        item.closest('.btn-group').find('button').html(item.html() + '<span class="caret"></span>');
                    }
                });
                if (newValue.type === 'empty') {
                    this.$(this.criteriaValueSelectors.value).hide();
                } else {
                    this.$(this.criteriaValueSelectors.value).show();
                }

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
            },

            /**
             * @inheritDoc
             */
            _onClickChoiceValue: function(e) {
                NumberFilter.prototype._onClickChoiceValue.apply(this, arguments);
                if ($(e.currentTarget).attr('data-input-toggle')) {
                    var parentDiv = $(e.currentTarget).parent().parent().parent().parent();
                    if ($(e.currentTarget).attr('data-value') === 'empty') {
                        parentDiv.find('input[name="value"]').hide();
                    } else {
                        parentDiv.find('input[name="value"]').show();
                    }
                }
            }
        });
    }
);
