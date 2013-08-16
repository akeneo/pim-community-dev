var Oro = Oro || {};
Oro.Filter = Oro.Filter || {};

/**
 * Date filter: filter type as option + interval begin and end dates
 *
 * @class   Oro.Filter.DateFilter
 * @extends Oro.Filter.ChoiceFilter
 */
Oro.Filter.DateFilter = Oro.Filter.ChoiceFilter.extend({
    /**
     * Template for filter criteria
     *
     * @property {function(Object, ?Object=): String}
     */
    popupCriteriaTemplate: _.template(
        '<div>' +
            '<div class="horizontal clearfix">' +
                '<select name="<%= name %>" class="filter-select-oro">' +
                    '<% _.each(choices, function (hint, value) { %>' +
                        '<option value="<%= value %>"><%= hint %></option>' +
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
     * Empty data object
     *
     * @property
     */
    emptyValue: {
        type: '',
        value: {
            start: '',
            end: ''
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
        changeMonth: true,
        changeYear:  true,
        yearRange:  '-80:+1',
        dateFormat: 'yy-mm-dd',
        altFormat:  'yy-mm-dd',
        className:      'date-filter-widget',
        showButtonPanel: true,
        currentText: 'Now'
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
        Oro.Filter.ChoiceFilter.prototype.initialize.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    _renderCriteria: function(el) {
        $(el).append(this.popupCriteriaTemplate({
            name: this.name,
            choices: this.choices,
            inputClass: this.inputClass
        }));

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
        return widget;
    },

    /**
     * @inheritDoc
     */
    _onClickOutsideCriteria: function(e) {
        var elements = [this.$(this.criteriaSelector)];

        var widget = $(this.dateWidgetSelector);
        elements.push(widget);
        elements = _.union(elements, widget.find('span'));

        var clickedElement = _.find(elements, function(elem) {
            return _.isEqual(elem.get(0), e.target) || elem.has(e.target).length;
        });

        if (!clickedElement && $(e.target).prop('tagName') == 'BUTTON') {
            clickedElement = e.target;
        }

        if (!clickedElement) {
            this._hideCriteria();
            this.setValue(this._readDOMValue());
        }
    },

    /**
     * @inheritDoc
     */
    _getCriteriaHint: function() {
        var value = this._getDisplayValue();
        if (value.value) {
            var hint = '';
            var start = value.value.start;
            var end   = value.value.end;
            var type  = value.type ? value.type.toString() : '';

            switch (type) {
                case this.typeValues.moreThan.toString():
                    hint += ' more than ' + start;
                    break;
                case this.typeValues.lessThan.toString():
                    hint += ' less than ' + start;
                    break;
                case this.typeValues.notBetween.toString():
                    if (start && end) {
                        hint += this.choices[this.typeValues.notBetween] + ' ' + start + ' and ' + end
                    } else if (start) {
                        hint += ' before ' + start;
                    } else if (end) {
                        hint += ' after ' + end;
                    }
                    break;
                case this.typeValues.between.toString():
                default:
                    if (start && end) {
                        hint += this.choices[this.typeValues.between] + ' ' + start + ' and ' + end
                    } else if (start) {
                        hint += ' from ' + start;
                    } else if (end) {
                        hint += ' to ' + end;
                    }
                    break;
            }
            if (hint) {
                return hint;
            }
        }

        return this.defaultCriteriaHint;
    },

    /**
     * @inheritDoc
     */
    _formatDisplayValue: function(value) {
        var fromFormat = this.dateWidgetOptions.altFormat;
        var toFormat = this.dateWidgetOptions.dateFormat;
        return this._formatValueDates(value, fromFormat, toFormat);
    },

    /**
     * @inheritDoc
     */
    _formatRawValue: function(value) {
        var fromFormat = this.dateWidgetOptions.dateFormat;
        var toFormat = this.dateWidgetOptions.altFormat;
        return this._formatValueDates(value, fromFormat, toFormat);
    },

    /**
     * Format datetes in a valut to another format
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
        }
    },

    /**
     * @inheritDoc
     */
    _focusCriteria: function() {},

    /**
     * @inheritDoc
     */
    _hideCriteria: function() {
        Oro.Filter.ChoiceFilter.prototype._hideCriteria.apply(this, arguments);
    },

    /**
     * @inheritDoc
     */
    _triggerUpdate: function(newValue, oldValue) {
        newValue = newValue.value;
        oldValue = oldValue.value;

        if ((newValue && (newValue.start || newValue.end)) ||
            (oldValue && (oldValue.start || oldValue.end))
        ) {
            this.trigger('update');
        }
    }
});
