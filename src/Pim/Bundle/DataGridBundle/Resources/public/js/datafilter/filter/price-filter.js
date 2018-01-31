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
                if (typeof(value.value) === 'object') {
                    this._setInputValue(this.criteriaValueSelectors.value, '');
                }
                this._setInputValue(this.criteriaValueSelectors.currency, value.currency);
                this._highlightDropdown(value.currency, '.currency');

                return this;
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
                this._highlightDropdown(newValue.currency, '.currency');
                if (_.contains(['empty', 'not empty'], newValue.type)) {
                    this._disableInput();
                } else {
                    this._enableInput();
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
                    if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                        this._disableInput();
                    } else {
                        this._enableInput();
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
                this._highlightDropdown(value, '.currency');

                e.preventDefault();
            },

            /**
             * {@inheritdoc}
             */
            _disableInput() {
                this.$el.find(this.criteriaValueSelectors.value).hide();
                this.$el.find('.AknFilterChoice-currency')
                    .addClass('AknFilterChoice-currency--centered');
            },

            /**
             * {@inheritdoc}
             */
            _enableInput() {
                this.$el.find(this.criteriaValueSelectors.value).show();
                this.$el.find('.AknFilterChoice-currency')
                    .removeClass('AknFilterChoice-currency--centered');
            }
        });
    }
);
