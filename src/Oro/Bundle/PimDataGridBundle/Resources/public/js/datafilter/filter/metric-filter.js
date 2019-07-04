define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/datafilter/number-filter',
        'oro/app',
        'pim/template/datagrid/filter/metric-filter'
    ],
    function (
        $,
        _,
        __,
        NumberFilter,
        app,
        template
    ) {
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
            events: {
                'keyup input': '_onReadCriteriaInputKey',
                'keydown [type="text"]': '_preventEnterProcessing',
                'click .filter-update': '_onClickUpdateCriteria',
                'click .filter-criteria-selector': '_onClickCriteriaSelector',
                'click .operator .AknDropdown-menuLink': '_onSelectOperator',
                'click .unit .AknDropdown-menuLink': '_onSelectUnit',
                'click .disable-filter': '_onClickDisableFilter'
            },
            units: [],

            /**
             * @inheritDoc
             */
            initialize: function() {
                NumberFilter.prototype.initialize.apply(this, arguments);

                this.emptyValue = {
                    unit: _.first(_.keys(this.units)),
                    type: _.findWhere(this.choices, { label: '=' }).data,
                    value: ''
                };

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
                $(el).append(
                    this.popupCriteriaTemplate({
                        __: __,
                        label: this.label,
                        operatorChoices: this._getOperatorChoices(),
                        selectedOperator: this._getDisplayValueOrDefault().type,
                        emptyChoice: this.emptyChoice,
                        selectedOperatorLabel: this._getOperatorChoices()[this._getDisplayValueOrDefault().type],
                        operatorLabel: __('pim_common.operator'),
                        updateLabel: __('pim_common.update'),
                        units: this.units,
                        unitLabel: __('pim_datagrid.filters.metric_filter.label'),
                        selectedUnit: this._getDisplayValueOrDefault().unit,
                        value: this._getDisplayValueOrDefault().value
                    })
                );
                return this;
            },

            /**
             * Returns the default values just for display
             */
            _getDisplayValueOrDefault: function () {
                const value = this._getDisplayValue();
                if ('' === value.unit) {
                    value.unit = this.emptyValue.unit;
                }
                if ('' === value.type) {
                    value.type = this.emptyValue.type;
                }

                return value;
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function (value) {
                NumberFilter.prototype._writeDOMValue.apply(this, arguments);
                this._setInputValue(this.criteriaValueSelectors.unit, value.unit);
                this._highlightDropdown(value.unit, '.unit');

                return this;
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function () {
                let value = NumberFilter.prototype._readDOMValue.apply(this, arguments);
                value.unit = this._getInputValue(this.criteriaValueSelectors.unit);

                return value;
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
                    const option = this._getChoiceOption(value.type);
                    return option.label + ' ' + value.value + ' ' + __(`pim_measure.units.${value.unit}`);
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
                this._highlightDropdown(newValue.unit, '.unit');
                if (_.contains(['empty', 'not empty'], newValue.type)) {
                    this.$(this.criteriaValueSelectors.value).hide();
                    this.$('.AknFilterChoice-unit').hide();
                } else {
                    this.$(this.criteriaValueSelectors.value).show();
                    this.$('.AknFilterChoice-unit').show();
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
                const filterContainer = $(e.currentTarget).closest('.filter-item');
                if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                    filterContainer.find(this.criteriaValueSelectors.value).hide();
                    filterContainer.find('.AknFilterChoice-unit').hide();
                } else {
                    filterContainer.find(this.criteriaValueSelectors.value).show();
                    filterContainer.find('.AknFilterChoice-unit').show();
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
             * Update the unit after clicking in the dropdown
             *
             * @param {Event} e
             */
            _onSelectUnit: function(e) {
                const value = $(e.currentTarget).find('.unit_choice').attr('data-value');
                $(this.criteriaValueSelectors.unit).val(value);
                this._highlightDropdown(value, '.unit');

                e.preventDefault();
            },

            _enableInput: function() {
                NumberFilter.prototype._enableInput.apply(this, arguments);
                this.$('.AknFilterChoice-inputContainer').show();
                this.$('.AknFilterChoice-unit').show();
                this._updateCriteriaSelectorPosition();
            },

            _disableInput: function() {
                NumberFilter.prototype._disableInput.apply(this, arguments);
                this.$('.AknFilterChoice-inputContainer').hide();
                this.$('.AknFilterChoice-unit').hide();
                this._updateCriteriaSelectorPosition();
            }
        });
    }
);
