/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/datafilter/choice-filter', 'oro/locale-settings'],
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
         * CSS class for visual datetime input elements
         *
         * @property
         */
        inputClass: 'datetime-visual-element',

        /**
         * Datetime widget options
         *
         * @property
         */
        dateWidgetOptions: {
            timeFormat:       localeSettings.getVendorDateTimeFormat('jquery_ui', 'time', 'HH:mm'),
            altFieldTimeOnly: false,
            altSeparator:     ' ',
            altTimeFormat:    'HH:mm',
            changeMonth:      true,
            changeYear:       true,
            yearRange:        '-80:+80',
            dateFormat:       localeSettings.getVendorDateTimeFormat('jquery_ui', 'date', 'mm/dd/yy'),
            altFormat:        'yy-mm-dd',
            className:        'date-filter-widget',
            showButtonPanel:   true
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
        dateWidgetSelector: 'div#ui-datepicker-div.ui-datepicker',

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
         * @inheritDoc
         */
        _initializeDateWidget: function(widgetSelector) {
            this.$(widgetSelector).datetimepicker(this.dateWidgetOptions);
            var widget = this.$(widgetSelector).datetimepicker('widget');
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
            var dateFromFormat = this.dateWidgetOptions.altFormat;
            var dateToFormat = this.dateWidgetOptions.dateFormat;
            var timeFromFormat = this.dateWidgetOptions.altTimeFormat;
            var timeToFormat = this.dateWidgetOptions.timeFormat;
            return this._formatValueDatetimes(value, dateFromFormat, dateToFormat, timeFromFormat, timeToFormat);
        },

        /**
         * @inheritDoc
         */
        _formatRawValue: function(value) {
            var dateFromFormat = this.dateWidgetOptions.dateFormat;
            var dateToFormat = this.dateWidgetOptions.altFormat;
            var timeFromFormat = this.dateWidgetOptions.timeFormat;
            var timeToFormat = this.dateWidgetOptions.altTimeFormat;
            return this._formatValueDatetimes(value, dateFromFormat, dateToFormat, timeFromFormat, timeToFormat);
        },

        /**
         * Format dates from a format to another format
         *
         * @param {Object} value
         * @param {String} fromFormat
         * @param {String} toFormat
         *
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
         *
         * @return {String}
         * @protected
         */
        _formatDate: function(value, fromFormat, toFormat) {
            var fromValue = $.datepicker.parseDate(fromFormat, value);
            if (!fromValue) {
                fromValue = $.datepicker.parseDate(toFormat, value);
                if (!fromValue) {
                    return value;
                }
            }
            return $.datepicker.formatDate(toFormat, fromValue);
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
        _triggerUpdate: function(newValue, oldValue) {
            if ((newValue.type === 'empty' || oldValue.type === 'empty') && newValue.type !== oldValue.type) {
                this.trigger('update');
                return;
            }

            newValue = newValue.value;
            oldValue = oldValue.value;

            if ((newValue && (newValue.start || newValue.end)) ||
                (oldValue && (oldValue.start || oldValue.end))
            ) {
                this.trigger('update');
            }
        },

        /**
         * @inheritDoc
         */
        setValue: function(value) {
            if (this._isValueValid(value)) {
                return ChoiceFilter.prototype.setValue.apply(this, arguments);
            }
            return this;
        },

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
         * Format datetimes in a valut to another format
         *
         * @param {Object} value
         * @param {String} dateFromFormat
         * @param {String} dateToFormat
         * @param {String} timeFromFormat
         * @param {String} timeToToFormat
         * @return {Object}
         * @protected
         */
        _formatValueDatetimes: function(value, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat) {
            if (value.value && value.value.start) {
                value.value.start = this._formatDatetime(
                    value.value.start, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat
                );
            }
            if (value.value && value.value.end) {
                value.value.end = this._formatDatetime(
                    value.value.end, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat
                );
            }
            return value;
        },

        /**
         * Formats datetime string to another format
         *
         * @param {String} value
         * @param {String} dateFromFormat
         * @param {String} dateToFormat
         * @param {String} timeFromFormat
         * @param {String} timeToToFormat
         * @return {String}
         * @protected
         */
        _formatDatetime: function(value, dateFromFormat, dateToFormat, timeFromFormat, timeToToFormat) {
            var datePart = this._formatDate(value, dateFromFormat, dateToFormat);
            var dateBefore = this._formatDate(datePart, dateToFormat, dateFromFormat);
            var timePart = value.substr(dateBefore.length + this.dateWidgetOptions.altSeparator.length);
            timePart = this._formatTime(timePart, timeFromFormat, timeToToFormat);
            return datePart + this.dateWidgetOptions.altSeparator + timePart;
        },

        /**
         * Formats time string to another format
         *
         * @param {String} value
         * @param {String} fromFormat
         * @param {String} toFormat
         * @return {String}
         * @protected
         */
        _formatTime: function(value, fromFormat, toFormat) {
            var fromValue = $.datepicker.parseTime(fromFormat, value);
            if (!fromValue) {
                fromValue = $.datepicker.parseTime(toFormat, value);
                if (!fromValue) {
                    return value;
                }
            }
            return $.datepicker.formatTime(toFormat, fromValue);
        }
    });
});
