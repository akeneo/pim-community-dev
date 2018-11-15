/* global define */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/datafilter/choice-filter',
        'datepicker',
        'pim/date-context',
        'pim/formatter/date',
        'pim/template/datagrid/filter/date-filter'
    ],
function(
    $,
    _,
    __,
    ChoiceFilter,
    Datepicker,
    DateContext,
    DateFormatter,
    template
) {
    'use strict';

    /**
     * Date filter: filter type as option + interval begin and end dates
     *
     * @export  oro/datafilter/date-filter
     * @class   oro.datafilter.DateFilter
     * @extends oro.datafilter.ChoiceFilter
     */
    return ChoiceFilter.extend({
        /**
         * Template for filter criteria
         *
         * @property {function(Object, ?Object=): String}
         */
        popupCriteriaTemplate: _.template(template),

        /**
         * Selectors for filter data
         *
         * @property
         */
        criteriaValueSelectors: {
            type: '.AknActionButton-highlight',
            value: {
                start: '.from',
                end: '.to'
            }
        },

        /**
         * CSS class for visual date input elements
         *
         * @property
         */
        inputClass: 'AknTextField',

        /**
         * Date widget options
         *
         * @property
         */
        datetimepickerOptions: {
            format: DateContext.get('date').format,
            defaultFormat: DateContext.get('date').defaultFormat,
            language: DateContext.get('language'),
        },

        /**
         * References to date widgets
         *
         * @property
         */
        dateWidgets: {
            start: null,
            end: null
        },

        /**
         * Date filter type values
         *
         * @property
         */
        typeValues: {
            between:    1,
            notBetween: 2,
            moreThan:   3,
            lessThan:   4
        },

        /**
         * Date widget selector
         *
         * @property
         */
        dateWidgetSelector: 'datepicker',

        events: {
            'click .filter-update': '_onClickUpdateCriteria',
            'click .filter-criteria-selector': '_onClickCriteriaSelector',
            'click .AknDropdown-menuLink': '_onSelectOperator',
            'click .disable-filter': '_onClickDisableFilter'
        },

        /**
         * @inheritDoc
         */
        initialize: function () {
            // init empty value object if it was not initialized so far
            if (_.isUndefined(this.emptyValue)) {
                this.emptyValue = {
                    type: (_.isEmpty(this.choices) ? '' : _.first(this.choices).value),
                    value: {
                        start: '',
                        end: ''
                    }
                };
            }

            ChoiceFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * @param {Event} e
         */
        _onSelectOperator: function (e) {
            const value = $(e.currentTarget).find('.operator_choice').attr('data-value');
            this._highlightDropdown(value, '.operator');
            this._displayFilterType(value);

            e.preventDefault();
        },

        /**
         * Manage how to display a filter type
         *
         * @param {String} type
         * @protected
         */
        _displayFilterType: function(type) {
            this.$el.find('.AknFilterChoice-separator').show();
            this.$el.find(this.criteriaValueSelectors.value.end).show();
            this.$el.find(this.criteriaValueSelectors.value.start).show();
            if (this.typeValues.moreThan == parseInt(type)) {
                this.$el.find('.AknFilterChoice-separator').hide();
                this.$el.find(this.criteriaValueSelectors.value.end).hide();
            } else if (this.typeValues.lessThan == parseInt(type)) {
                this.$el.find('.AknFilterChoice-separator').hide();
                this.$el.find(this.criteriaValueSelectors.value.start).hide();
            } else if (_.contains(['empty', 'not empty'], type)) {
                this.$el.find('.AknFilterChoice-separator').hide();
                this.$el.find(this.criteriaValueSelectors.value.end).hide();
                this.$el.find(this.criteriaValueSelectors.value.start).hide();
            }
        },

        /**
         * @inheritDoc
         */
        _renderCriteria: function(el) {
            const value = this._getDisplayValue();
            $(el).empty().append(
                this.popupCriteriaTemplate({
                    label: this.label,
                    name: this.name,
                    choices: this.choices,
                    selectedChoice: value.type,
                    inputClass: this.inputClass,
                    selectedOperatorLabel: _.findWhere(this.choices, {value: value.type + ''}).label,
                    operatorLabel: __('pim_datagrid.filters.common.operator'),
                    updateLabel: __('pim_common.update'),
                    fromLabel: __('pim_common.from'),
                    toLabel: __('pim_common.to'),
                    from: value.value.start,
                    to: value.value.end,
                })
            );

            _.each(this.criteriaValueSelectors.value, function(selector, name) {
                this.dateWidgets[name] = Datepicker.init(this.$(selector), this.datetimepickerOptions);
            }, this);

            this._displayFilterType(value.type);

            return this;
        },

        /**
         * @inheritDoc
         */
        _getCriteriaHint: function() {
            var hint = '',
                option, start, end, type,
                value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();

            if (_.contains(['empty', 'not empty'], value.type)) {
                return this._getChoiceOption(value.type).label;
            }

            if (value.value) {
                start = value.value.start;
                end   = value.value.end;
                type  = value.type ? value.type.toString() : '';

                switch (type) {
                    case this.typeValues.moreThan.toString():
                        hint += [__('pim_common.more_than'), start].join(' ');
                        break;
                    case this.typeValues.lessThan.toString():
                        hint += [__('pim_common.less_than'), end].join(' ');
                        break;
                    case this.typeValues.notBetween.toString():
                        if (start && end) {
                            option = this._getChoiceOption(this.typeValues.notBetween);
                            hint += [option.label, start, __('pim_common.and'), end].join(' ');
                        } else if (start) {
                            hint += [__('pim_common.before'), start].join(' ');
                        } else if (end) {
                            hint += [__('pim_common.after'), end].join(' ');
                        }
                        break;
                    case this.typeValues.between.toString():
                    default:
                        if (start && end) {
                            option = this._getChoiceOption(this.typeValues.between);
                            hint += [option.label, start, __('pim_common.and'), end].join(' ');
                        } else if (start) {
                            hint += [__('pim_common.from'), start].join(' ');
                        } else if (end) {
                            hint += [__('pim_common.to'), end].join(' ');
                        }
                        break;
                }
                if (hint) {
                    return hint;
                }
            }

            return this.placeholder;
        },

        /**
         * @inheritDoc
         */
        _formatDisplayValue: function(value) {
            _.each(value.value, function(dateValue, name) {
                if (dateValue) {
                    value.value[name] = DateFormatter.format(
                        dateValue,
                        this.datetimepickerOptions.defaultFormat,
                        this.datetimepickerOptions.format
                    );
                }
            }, this);

            return value;
        },

        /**
         * @inheritDoc
         */
        _formatRawValue: function(value) {
            _.each(value.value, function(dateValue, name) {
                if (dateValue) {
                    value.value[name] = DateFormatter.format(
                        dateValue,
                        this.datetimepickerOptions.format,
                        this.datetimepickerOptions.defaultFormat
                    );
                }
            }, this);

            return value;
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.criteriaValueSelectors.value.start + ' input', value.value.start);
            this._setInputValue(this.criteriaValueSelectors.value.end + ' input', value.value.end);
            this._setInputValue(this.criteriaValueSelectors.type, value.type);

            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            const operator = this.$('.active .operator_choice').data('value');

            return {
                type: operator,
                value: {
                    start: this._getInputValue(this.criteriaValueSelectors.value.start + ' input'),
                    end:   this._getInputValue(this.criteriaValueSelectors.value.end + ' input')
                }
            };
        },

        /**
         * @inheritDoc
         */
        _focusCriteria: function() {},

        /**
         * @inheritDoc
         */
        _hideCriteria: function() {
            ChoiceFilter.prototype._hideCriteria.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        _triggerUpdate: function(newValue, oldValue) {},

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            ChoiceFilter.prototype._onValueUpdated.apply(this, arguments);
            if (_.contains(['empty', 'not empty'], newValue.type)) {
                this.$el.find('.AknFilterChoice-separator').hide().end()
                    .find(this.criteriaValueSelectors.value.end).hide().end()
                    .find(this.criteriaValueSelectors.value.start).hide();
            } else {
                this._displayFilterType(newValue.type);
            }
        },

        /**
         * @inheritDoc
         */
        _onClickUpdateCriteria: function(e) {
            this._hideCriteria();
            this.setValue(this._formatRawValue(this._readDOMValue()));
            this.trigger('update');
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
});
