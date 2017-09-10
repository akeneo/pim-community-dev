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
                this.$('.choicefilter button.dropdown-toggle').first().html(_.__('Action') + '<span class="AknActionButton-caret AknCaret"></span>');
                this.$('.choicefilter button.dropdown-toggle').last().html(_.__('Currency') + '<span class="AknActionButton-caret AknCaret"></span>');
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
                if (_.contains(['empty', 'not empty'], value.type) && value.currency) {
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
                '<div class="AknFilterChoice currencyfilter choicefilter">' +
                    '<div class="AknFilterChoice-operator AknDropdown">' +
                        '<button class="AknActionButton AknActionButton--big AknActionButton--noRightBorder dropdown-toggle" data-toggle="dropdown">' +
                            '<%= _.__("Action") %>' +
                            '<span class="AknActionButton-caret AknCaret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">' +
                            '<% _.each(choices, function (option) { %>' +
                                '<li><a class="choice_value" href="#" data-value="<%= option.value %>" data-input-toggle="true"><%= option.label %></a></li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<input class="name_input" type="hidden" name="currency_type" value=""/>' +
                    '</div>' +
                    '<input class="AknTextField AknTextField--noRadius AknFilterChoice-field" type="text" name="value" value="">' +
                    '<div class="AknDropdown">' +
                        '<button class="AknActionButton AknActionButton--big AknActionButton--noRightBorder AknActionButton--noLeftBorder dropdown-toggle" data-toggle="dropdown">' +
                            '<%= _.__("Currency") %>' +
                            '<span class="AknActionButton-caret AknCaret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">' +
                            '<% _.each(currencies, function (currency) { %>' +
                                '<li><a class="choice_value" href="#" data-value="<%= currency %>"><%= currency %></a></li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<input class="name_input" type="hidden" name="currency_currency" value=""/>' +
                    '</div>' +
                    '<button class="AknFilterChoice-button AknButton AknButton--apply AknButton--noLeftRadius filter-update" type="button"><%= _.__("Update") %></button>' +
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
             * @property {Object}
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
                       (_.contains(['empty', 'not empty'], value.type) && value.currency);
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
                        item.closest('.AknDropdown').find('AknActionButton').html(item.html() + '<span class="AknActionButton-caret AknCaret"></span>');
                    }
                });
                if (_.contains(['empty', 'not empty'], newValue.type)) {
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
                if ($(e.currentTarget).attr('data-input-toggle')) {
                    var filterContainer = $(e.currentTarget).closest('.filter-item');
                    if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                        filterContainer.find(this.criteriaValueSelectors.value).hide();
                    } else {
                        filterContainer.find(this.criteriaValueSelectors.value).show();
                    }
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
