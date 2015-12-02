/* global define */
define(
    ['jquery', 'underscore', 'oro/translator', 'oro/datafilter/choice-filter', 'pim/date-context', 'bootstrap.datetimepicker'],
function($, _, __, ChoiceFilter, DateContext) {
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
                '<div class="horizontal clearfix type">' +
                    '<select name="<%= name %>" class="filter-select-oro">' +
                        '<% _.each(choices, function (option) { %>' +
                        '<option value="<%= option.value %>"<% if (option.value == selectedChoice) { %> selected="selected"<% } %>><%= option.label %></option>' +
                        '<% }); %>' +
                    '</select>' +
                '</div>' +
                '<div>' +
                    '<span class="start"><input type="text" value="" class="<%= inputClass %> add-on" name="start" placeholder="from"></span>' +
                    '<span class="filter-separator">-</span>' +
                    '<span class="end"><input type="text" value="" class="<%= inputClass %> add-on" name="end" placeholder="to"></span>' +
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
            type: '.type',
            value: {
                start: '.start',
                end: '.end'
            }
        },

        /**
         * Values for filter data
         *
         * "format" is used to stock localized date
         * "defaultFormat" is used to stock the default pattern
         *
         * @property
         */
        values: {
            type: null,
            value: {
                start: {
                    format: null,
                    defaultFormat: null
                },
                end: {
                    format: null,
                    defaultFormat: null
                }
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
        datetimepickerOptions: {
            format: DateContext.get('date').format,
            defaultFormat: DateContext.get('date').defaultFormat,
            locale: DateContext.get('language'),
            pickTime: false
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
        dateWidgetSelector: 'div.datepicker',

        /**
         * @inheritDoc
         */
        initialize: function () {
            _.extend(this.datetimepickerOptions, this.externalWidgetOptions);
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
            this.$el.find('.filter-separator').show().end().find('span').show();
            if (this.typeValues.moreThan == parseInt(type)) {
                this.$el.find('.filter-separator').hide().end().find(this.criteriaValueSelectors.value.end).hide();
            } else if (this.typeValues.lessThan == parseInt(type)) {
                this.$el.find('.filter-separator').hide().end().find(this.criteriaValueSelectors.value.start).hide();
            } else if ('empty' === type) {
                this.$el.find('.filter-separator').hide().end()
                    .find(this.criteriaValueSelectors.value.end).hide().end()
                    .find(this.criteriaValueSelectors.value.start).hide();
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
            var widget = this.$(widgetSelector);
            widget.datetimepicker(this.datetimepickerOptions);
            widget.addClass(this.datetimepickerOptions.className);

            var picker = widget.data('datetimepicker');
            widget.on('changeDate', function(e) {
                picker.format = this.datetimepickerOptions.defaultFormat;
                this.values.value[e.target.className].defaultFormat = picker.formatDate(e.date);

                picker.format = this.datetimepickerOptions.format;
                this.values.value[e.target.className].format = picker.formatDate(e.date);
            }.bind(this));

            return widget;
        },

        /**
         * @inheritDoc
         */
        _getInputValue: function(node) {
            var $select = this.$(node).find('select');

            return $select.val();
        },

        /**
         * @inheritDoc
         */
        _getCriteriaHint: function() {
            var hint = '',
                option, start, end, type,
                value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this.values;

            if (value.type === 'empty') {
                return this._getChoiceOption(value.type).label;
            }

            if (value.value) {
                start = value.value.start.format;
                end   = value.value.end.format;
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
                    start: this.values.value.start.defaultFormat,
                    end:   this.values.value.end.defaultFormat
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
                this.$el.find('.filter-separator').hide().end()
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
