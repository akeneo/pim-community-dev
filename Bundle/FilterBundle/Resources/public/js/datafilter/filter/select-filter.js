/* global define */
define(['underscore', 'oro/translator', 'oro/datafilter/abstract-filter', 'oro/multiselect-decorator'],
function(_, __, AbstractFilter, MultiselectDecorator) {
    'use strict';

    /**
     * Select filter: filter value as select option
     *
     * @export  oro/datafilter/select-filter
     * @class   oro.datafilter.SelectFilter
     * @extends oro.datafilter.AbstractFilter
     */
    return AbstractFilter.extend({
        /**
         * Filter template
         *
         * @property
         */
        template: _.template(
            '<div class="btn filter-select filter-criteria-selector">' +
                '<%= label %>: ' +
                '<select>' +
                    '<% _.each(options, function (option) { %><option value="<%= option.value %>"><%= option.label %></option><% }); %>' +
                '</select>' +
            '</div>' +
            '<a href="<%= nullLink %>" class="disable-filter"><i class="icon-remove hide-text"><%- _.__("Close") %></i></a>'
        ),

        /**
         * Filter content options
         *
         * @property
         */
        choices: [],

        /**
         * Placeholder for default value
         *
         * @property
         */
        placeholder: 'All',

        /**
         * Should default value be added to options list
         *
         * @property
         */
        populateDefault: true,

        /**
         * Selector for filter area
         *
         * @property
         */
        containerSelector: '.filter-select',

        /**
         * Selector for close button
         *
         * @property
         */
        disableSelector: '.disable-filter',

        /**
         * Selector for widget button
         *
         * @property
         */
        buttonSelector: '.select-filter-widget.ui-multiselect:first',

        /**
         * Selector for select input element
         *
         * @property
         */
        inputSelector: 'select',

        /**
         * Select widget object
         *
         * @property
         */
        selectWidget: null,

        /**
         * Minimum widget menu width, calculated depends on filter options
         *
         * @property
         */
        minimumWidth: null,

        /**
         * Select widget options
         *
         * @property
         */
        widgetOptions: {
            multiple: false,
            classes: 'select-filter-widget'
        },

        /**
         * Filter value object
         *
         * @property
         */
        emptyValue: {
            value: ''
        },

        /**
         * Select widget menu opened flag
         *
         * @property
         */
        selectDropdownOpened: false,

        /**
         * @property {Boolean}
         */
        contextSearch: true,

        /**
         * Filter events
         *
         * @property
         */
        events: {
            'keydown select': '_preventEnterProcessing',
            'click .filter-select': '_onClickFilterArea',
            'click .disable-filter': '_onClickDisableFilter',
            'change select': '_onSelectChange'
        },

        /**
         * Initialize.
         *
         * @param {Object} options
         */
        initialize: function() {
            // temp code to keep backward compatible
            this.choices = _.map(this.choices, function(option, i) {
                return _.isString(option) ? {value: i, label: option} : option;
            });
            AbstractFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * Render filter template
         *
         * @return {*}
         */
        render: function () {
            var options =  this.choices.slice(0);
            this.$el.empty();

            if (this.populateDefault) {
                options.unshift({value: '', label: this.placeholder});
            }

            this.$el.append(
                this.template({
                    label: this.label,
                    options: options,
                    placeholder: this.placeholder,
                    nullLink: this.nullLink
                })
            );

            this._initializeSelectWidget();

            return this;
        },

        /**
         * Initialize multiselect widget
         *
         * @protected
         */
        _initializeSelectWidget: function() {
            this.selectWidget = new MultiselectDecorator({
                element: this.$(this.inputSelector),
                parameters: _.extend({
                    noneSelectedText: this.placeholder,
                    selectedText: _.bind(function(numChecked, numTotal, checkedItems) {
                        return this._getSelectedText(checkedItems);
                    }, this),
                    position: {
                        my: 'left top+2',
                        at: 'left bottom',
                        of: this.$(this.containerSelector)
                    },
                    open: _.bind(function() {
                        this.selectWidget.onOpenDropdown();
                        this._setDropdownWidth();
                        this._setButtonPressed(this.$(this.containerSelector), true);
                        this.selectDropdownOpened = true;
                    }, this),
                    close: _.bind(function() {
                        this._setButtonPressed(this.$(this.containerSelector), false);
                        setTimeout(_.bind(function() {
                            this.selectDropdownOpened = false;
                        }, this), 100);
                    }, this)
                }, this.widgetOptions),
                contextSearch: this.contextSearch
            });

            this.selectWidget.setViewDesign(this);
            this.$(this.buttonSelector).append('<span class="caret"></span>');
        },

        /**
         * Get text for filter hint
         *
         * @param {Array} checkedItems
         * @protected
         */
        _getSelectedText: function(checkedItems) {
            if (_.isEmpty(checkedItems)) {
                return this.placeholder;
            }

            var elements = [];
            _.each(checkedItems, function(element) {
                var title = element.getAttribute('title');
                if (title) {
                    elements.push(title);
                }
            });
            return elements.join(', ');
        },

        /**
         * Set design for select dropdown
         *
         * @protected
         */
        _setDropdownWidth: function() {
            if (!this.minimumWidth) {
                this.minimumWidth = this.selectWidget.getMinimumDropdownWidth() + 22;
            }
            var widget = this.selectWidget.getWidget(),
                filterWidth = this.$(this.containerSelector).width(),
                requiredWidth = Math.max(filterWidth + 10, this.minimumWidth);
            widget.width(requiredWidth).css('min-width', requiredWidth + 'px');
            widget.find('input[type="search"]').width(requiredWidth - 22);
        },

        /**
         * Open/close select dropdown
         *
         * @param {Event} e
         * @protected
         */
        _onClickFilterArea: function(e) {
            if (!this.selectDropdownOpened) {
                setTimeout(_.bind(function() {
                    this.selectWidget.multiselect('open');
                }, this), 50);
            } else {
                setTimeout(_.bind(function() {
                    this.selectWidget.multiselect('close');
                }, this), 50);
            }

            e.stopPropagation();
        },

        /**
         * Triggers change data event
         *
         * @protected
         */
        _onSelectChange: function() {
            // set value
            this.setValue(this._formatRawValue(this._readDOMValue()));

            // update dropdown
            var widget = this.$(this.containerSelector);
            this.selectWidget.updateDropdownPosition(widget);
        },

        /**
         * Handle click on filter disabler
         *
         * @param {Event} e
         */
        _onClickDisableFilter: function(e) {
            e.preventDefault();
            this.disable();
        },

        /**
         * @inheritDoc
         */
        _isNewValueUpdated: function(newValue) {
            return !_.isEqual(this.getValue().value || '', newValue.value);
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            AbstractFilter.prototype._onValueUpdated.apply(this, arguments);
            this.selectWidget.multiselect('refresh');
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.inputSelector, value.value);
            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {
                value: this._getInputValue(this.inputSelector)
            };
        }
    });
});
