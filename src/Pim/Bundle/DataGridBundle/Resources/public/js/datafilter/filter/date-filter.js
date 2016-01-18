/* global define */
define(
    ['jquery', 'underscore', 'oro/translator', 'oro/datafilter/choice-filter', 'oro/locale-settings', 'bootstrap.bootstrapsdatepicker'],
function($, _, __, ChoiceFilter, localeSettings) {
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
        popupCriteriaTemplate: _.template(
            '<div>' +
                '<div class="horizontal clearfix">' +
                    '<select name="<%= name %>" class="filter-select-oro">' +
                        '<% _.each(choices, function (option) { %>' +
                            '<option value="<%= option.value %>"<% if (option.value == selectedChoice) { %> selected="selected"<% } %>><%= option.label %></option>' +
                        '<% }); %>' +
                    '</select>' +
                '</div>' +
                '<div>' +
                    '<input type="text" class="<%= inputClass %>" value="" name="start" placeholder="from">' +
                    '<span class="filter-separator">-</span>' +
                    '<input type="text" class="<%= inputClass %>" value="" name="end" placeholder="to">' +
                '</div>' +
                '<div class="oro-action">' +
                    '<div class="btn-group">' +
                        '<button class="btn btn-primary filter-update" type="button"><%- _.__("Update") %></button>' +
                    '</div>' +
                '</div>' +
            '</div>'
        ),

        /**
         * Selectors for filter data
         *
         * @property
         */
        criteriaValueSelectors: {
            type: 'select',
            value: {
                start: 'input[name="start"]',
                end:   'input[name="end"]'
            }
        },

        /**
         * CSS class for visual date input elements
         *
         * @property
         */
        inputClass: 'date-visual-element',

        /**
         * Date widget options
         *
         * @property
         */
        dateWidgetOptions: {
            todayHighlight: true,
            format: localeSettings.getVendorDateTimeFormat('jquery_ui', 'date', 'mm/dd/yy'),
            defaultFormat: 'yyyy-mm-dd',
            language: 'en',
            todayBtn: true,
            autoclose: true
        },

        /**
         * Additional date widget options that might be passed to filter
         * http://api.jqueryui.com/datepicker/
         *
         * @property
         */
        externalWidgetOptions: {},

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
        dateWidgetSelector: '.datepicker',

        /**
         * @inheritDoc
         */
        initialize: function () {
            _.extend(this.dateWidgetOptions, this.externalWidgetOptions);
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

        changeFilterType: function (e) {
            var select = this.$el.find(e.currentTarget);
            var selectedValue = select.val();

            this._displayFilterType(selectedValue);
        },

        /**
         * Manage how to display a filter type
         *
         * @param {String} type
         * @protected
         */
        _displayFilterType: function(type) {
            this.$el.find('.filter-separator').show().end().find('input').show();
            if (this.typeValues.moreThan == parseInt(type)) {
                this.$el.find('.filter-separator').hide().end().find(this.criteriaValueSelectors.value.end).hide();
            } else if (this.typeValues.lessThan == parseInt(type)) {
                this.$el.find('.filter-separator').hide().end().find(this.criteriaValueSelectors.value.start).hide();
            } else if ('empty' === type) {
                this.$el.find('.filter-separator').hide().end().find(this.criteriaValueSelectors.value.end).hide().end().find(this.criteriaValueSelectors.value.start).hide();
            }
        },

        /**
         * @inheritDoc
         */
        _renderCriteria: function(el) {
            $(el).append(
                this.popupCriteriaTemplate({
                    name: this.name,
                    choices: this.choices,
                    selectedChoice: this.emptyValue.type,
                    inputClass: this.inputClass
                })
            );

            $(el).find('select:first').bind('change', _.bind(this.changeFilterType, this));

            _.each(this.criteriaValueSelectors.value, function(actualSelector, name) {
                this.dateWidgets[name] = this._initializeDateWidget(actualSelector);
            }, this);

            return this;
        },

        /**
         * Initialize date widget
         *
         * @param {String} widgetSelector
         * @return {*}
         * @protected
         */
        _initializeDateWidget: function(widgetSelector) {
            this.$(widgetSelector).datepicker(this.dateWidgetOptions);
            var widget = this.$(widgetSelector).datepicker('widget');
            widget.addClass(this.dateWidgetOptions.className);
            $(this.dateWidgetSelector).on('click', function(e) {
                e.stopImmediatePropagation();
            });
            return widget;
        },

        /**
         * @inheritDoc
         */
        _getCriteriaHint: function() {
            var hint = '',
                option, start, end, type,
                value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
            if (value.type === 'empty') {
                return this._getChoiceOption(value.type).label;
            }
            if (value.value) {
                start = value.value.start;
                end   = value.value.end;
                type  = value.type ? value.type.toString() : '';

                switch (type) {
                    case this.typeValues.moreThan.toString():
                        hint += [__('more than'), start].join(' ');
                        break;
                    case this.typeValues.lessThan.toString():
                        hint += [__('less than'), end].join(' ');
                        break;
                    case this.typeValues.notBetween.toString():
                        if (start && end) {
                            option = this._getChoiceOption(this.typeValues.notBetween);
                            hint += [option.label, start, __('and'), end].join(' ');
                        } else if (start) {
                            hint += [__('before'), start].join(' ');
                        } else if (end) {
                            hint += [__('after'), end].join(' ');
                        }
                        break;
                    case this.typeValues.between.toString():
                    default:
                        if (start && end) {
                            option = this._getChoiceOption(this.typeValues.between);
                            hint += [option.label, start, __('and'), end].join(' ');
                        } else if (start) {
                            hint += [__('from'), start].join(' ');
                        } else if (end) {
                            hint += [__('to'), end].join(' ');
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
            var fromFormat = this.dateWidgetOptions.defaultFormat;
            var toFormat = this.dateWidgetOptions.format;
            return this._formatValueDates(value, fromFormat, toFormat);
        },

        /**
         * @inheritDoc
         */
        _formatRawValue: function(value) {
            var fromFormat = this.dateWidgetOptions.format;
            var toFormat = this.dateWidgetOptions.defaultFormat;
            return this._formatValueDates(value, fromFormat, toFormat);
        },

        /**
         * Format dates in a valut to another format
         *
         * @param {Object} value
         * @param {String} fromFormat
         * @param {String} toFormat
         * @return {Object}
         * @protected
         */
        _formatValueDates: function(value, fromFormat, toFormat) {
            if (value.value && value.value.start) {
                value.value.start = this._formatDate(value.value.start, fromFormat, toFormat);
            }
            if (value.value && value.value.end) {
                value.value.end = this._formatDate(value.value.end, fromFormat, toFormat);
            }
            return value;
        },

        /**
         * Formats date string to another format
         *
         * @param {String} value
         * @param {String} fromFormat
         * @param {String} toFormat
         * @return {String}
         * @protected
         */
        _formatDate: function(value, fromFormat, toFormat) {
            var dpg = $.fn.datepicker.DPGlobal;

            return dpg.formatDate(new Date(dpg.parseDate(value, fromFormat, 'en')), toFormat, 'en');
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.criteriaValueSelectors.value.start, value.value.start);
            this._setInputValue(this.criteriaValueSelectors.value.end, value.value.end);
            this._setInputValue(this.criteriaValueSelectors.type, value.type);
            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {
                type: this._getInputValue(this.criteriaValueSelectors.type),
                value: {
                    start: this._getInputValue(this.criteriaValueSelectors.value.start),
                    end:   this._getInputValue(this.criteriaValueSelectors.value.end)
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
        setValue: function(value) {
            if (this._isValueValid(value)) {
                return ChoiceFilter.prototype.setValue.apply(this, arguments);
            }
            return this;
        },

        /**
         * @inheritDoc
         */
        _isValueValid: function(value) {
            if (_.isEqual(value, this.emptyValue) && !_.isEqual(this.value, value)) {
                return true;
            }
            return value.type === 'empty' || value.value.start || value.value.end;
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            ChoiceFilter.prototype._onValueUpdated.apply(this, arguments);
            if ('empty' === newValue.type) {
                this.$el.find('.filter-separator').hide().end().find(this.criteriaValueSelectors.value.end).hide().end().find(this.criteriaValueSelectors.value.start).hide();
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
        },

        _onClickOutsideCriteria: function(e) {
            var elem = this.$(this.criteriaSelector);
            var isDatepicker = false;
            $.each(e.originalEvent.path, function (key, value) {
               if ($(value).hasClass('datepicker')) {
                   isDatepicker = true;
               }
            });

            if (elem.get(0) !== e.target && !elem.has(e.target).length && false === isDatepicker) {
                this._hideCriteria();
                this.setValue(this._formatRawValue(this._readDOMValue()));
                e.stopPropagation();
            }
        }
    });
});
