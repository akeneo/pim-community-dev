define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/datafilter/number-filter',
        'oro/app',
        'pim/template/datagrid/filter/price-filter'
    ], function (
        $,
        _,
        __,
        NumberFilter,
        app,
        popupCriteriaTemplate
    ) {
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
            popupCriteriaTemplate: _.template(popupCriteriaTemplate),
            currencies: [],
            criteriaValueSelectors: {
                currency: 'input[name="currency_currency"]',
                type:     'input[name="currency_type"]',
                value:    'input[name="value"]'
            },
            events: {
                'keyup input': '_onReadCriteriaInputKey',
                'keydown [type="text"]': '_preventEnterProcessing',
                'click .filter-update': '_onClickUpdateCriteria',
                'click .filter-criteria-selector': '_onClickCriteriaSelector',
                'click .operator .AknDropdown-menuLink': '_onSelectOperator',
                'click .currency .AknDropdown-menuLink': '_onSelectCurrency',
                'click .disable-filter': '_onClickDisableFilter'
            },

            /**
             * @inheritDoc
             */
            initialize: function() {
                NumberFilter.prototype.initialize.apply(this, arguments);

                this.emptyValue = {
                    currency: _.first(_.keys(this.currencies)),
                    type: _.findWhere(this.choices, { label: '=' }).data,
                    value: ''
                };

                this.on('disable', this._onDisable, this);
            },

            _onDisable: function() {
                this.$('.choicefilter button.dropdown-toggle').first().html(__('Action') + '<span class="AknActionButton-caret AknCaret"></span>');
                this.$('.choicefilter button.dropdown-toggle').last().html(__('Currency') + '<span class="AknActionButton-caret AknCaret"></span>');
            },

            /**
             * @inheritDoc
             */
            _renderCriteria: function(el) {
                $(el).append(
                    this.popupCriteriaTemplate({
                        label: this.label,
                        operatorChoices: this._getOperatorChoices(),
                        selectedOperator: this._getDisplayValue().type,
                        emptyChoice: this.emptyChoice,
                        selectedOperatorLabel: this._getOperatorChoices()[this._getDisplayValue().type],
                        operatorLabel: __('pim.grid.choice_filter.operator'),
                        updateLabel: __('Update'),
                        currencies: this.currencies,
                        currencyLabel: __('pim.grid.price_filter.label'),
                        selectedCurrency: this._getDisplayValue().currency,
                        value: this._getDisplayValue().value
                    })
                );
                return this;
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function (value) {
                NumberFilter.prototype._writeDOMValue.apply(this, arguments);
                this._setInputValue(this.criteriaValueSelectors.currency, value.currency);
                this._highlightCurrency(value.currency);

                return this;
            },

            /**
             * Highlights the current currency
             *
             * @param currency
             */
            _highlightCurrency(currency) {
                this.$el.find('.currency .AknDropdown-menuLink')
                    .removeClass('AknDropdown-menuLink--active')
                    .removeClass('active');

                const currentCurrencyChoice = this.$el.find('.currency .currency_choice[data-value=' + currency + ']');
                currentCurrencyChoice.parent()
                    .addClass('AknDropdown-menuLink--active')
                    .addClass('active');

                this.$el.find('.currency .AknActionButton-highlight').html(currentCurrencyChoice.text());
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function () {
                let value = NumberFilter.prototype._readDOMValue.apply(this, arguments);
                value.currency = this._getInputValue(this.criteriaValueSelectors.currency);

                return value;
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
                    const option = this._getChoiceOption(value.type);
                    return option.label + ' ' + value.value + ' ' + value.currency;
                }
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
                    const oldValue = this.value;
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
                    const filterContainer = $(e.currentTarget).closest('.filter-item');
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
            },

            /**
             * Update the currency after clicking in the dropdown
             *
             * @param {Event} e
             */
            _onSelectCurrency: function(e) {
                const value = $(e.currentTarget).find('.currency_choice').attr('data-value');
                $(this.criteriaValueSelectors.currency).val(value);
                this._highlightCurrency(value);

                e.preventDefault();
            }
        });
    }
);
