/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/app', 'oro/datafilter/text-filter', 'pim/initselect2', 'jquery.select2'],
function($, _, __, app, TextFilter, initSelect2) {
    'use strict';

    /**
     * Choice filter: filter type as option + filter value as string
     *
     * @export  oro/datafilter/choice-filter
     * @class   oro.datafilter.ChoiceFilter
     * @extends oro.datafilter.TextFilter
     */
    return TextFilter.extend({
        /**
         * Template for filter criteria
         *
         * @property {function(Object, ?Object=): String}
         */
        popupCriteriaTemplate: _.template(
            '<div class="AknFilterChoice choicefilter">' +
                '<div class="AknFilterChoice-operator AknDropdown">' +
                    '<button class="AknActionButton AknActionButton--big AknActionButton--noRightBorder dropdown-toggle" data-toggle="dropdown">' +
                        '<%= selectedChoiceLabel %>' +
                        '<span class="AknActionButton-caret AknCaret"></span>' +
                    '</button>' +
                    '<ul class="dropdown-menu">' +
                        '<% _.each(choices, function (option) { %>' +
                            '<li<% if (selectedChoice == option.value) { %> class="active"<% } %>><a class="choice_value" href="#" data-value="<%= option.value %>"><%= option.label %></a></li>' +
                        '<% }); %>' +
                    '</ul>' +
                    '<input class="name_input" type="hidden" name="<%= name %>" id="<%= name %>" value="<%= selectedChoice %>"/>' +
                '</div>' +
                '<input type="text" class="AknTextField AknTextField--noRadius AknFilterChoice-field select-field" name="value" value="">' +
                '<button class="AknFilterChoice-button AknButton AknButton--apply AknButton--noLeftRadius filter-update" type="button"><%- _.__("Update") %></button>' +
            '</div>'
        ),

        /**
         * Selectors for filter criteria elements
         *
         * @property {Object}
         */
        criteriaValueSelectors: {
            value: 'input[name="value"]',
            type: 'input[type="hidden"]'
        },

        /**
         * Filter events
         *
         * @property
         */
        events: {
            'keyup input': '_onReadCriteriaInputKey',
            'keydown [type="text"]': '_preventEnterProcessing',
            'click .filter-update': '_onClickUpdateCriteria',
            'click .filter-criteria-selector': '_onClickCriteriaSelector',
            'click .filter-criteria .filter-criteria-hide': '_onClickCloseCriteria',
            'click .disable-filter': '_onClickDisableFilter',
            'click .choice_value': '_onClickChoiceValue'
        },

        /**
         * Initialize.
         *
         * @param {Object} options
         */
        initialize: function() {
            // init filter content options if it was not initialized so far
            if (_.isUndefined(this.choices)) {
                this.choices = [];
            }
            // temp code to keep backward compatible
            if ($.isPlainObject(this.choices)) {
                this.choices = _.map(this.choices, function(option, i) {
                    return {value: i.toString(), label: option};
                });
            }

            // init empty value object if it was not initialized so far
            if (_.isUndefined(this.emptyValue)) {
                this.emptyValue = {
                    type: (_.isEmpty(this.choices) ? '' : _.first(this.choices).value),
                    value: ''
                };
            }

            TextFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        _renderCriteria: function(el) {
            var selectedChoice = this.emptyValue.type;
            var selectedChoiceLabel = '';
            if (!_.isEmpty(this.choices)) {
                var foundChoice = _.find(this.choices, function(choice) {
                    return (choice.value == selectedChoice);
                });
                selectedChoiceLabel = foundChoice.label;
            }
            $(el).append(
                this.popupCriteriaTemplate({
                    name: this.name,
                    choices: this.choices,
                    selectedChoice: selectedChoice,
                    selectedChoiceLabel: selectedChoiceLabel
                })
            );
            return this;
        },

        /**
         * @inheritDoc
         */
        _getCriteriaHint: function() {
            var option, hint,
                value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
            if (_.contains(['empty', 'not empty'], value.type)) {
                return this._getChoiceOption(value.type).label;
            }
            if (!value.value) {
                hint = this.placeholder;
            } else {
                option = this._getChoiceOption(value.type);
                hint = (option ? option.label + ' ' : '') + '"' + value.value + '"';
            }
            return hint;
        },

        /**
         * Fetches option object for corresponded value type
         *
         * @param {*|string} valueType
         * @returns {{value: string, label: string}}
         * @private
         */
        _getChoiceOption: function(valueType) {
            return _.findWhere(this.choices, {value: valueType.toString()});
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.criteriaValueSelectors.value, value.value);
            this._setInputValue(this.criteriaValueSelectors.type, value.type);
            return this;
        },


        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {
                value: this._getInputValue(this.criteriaValueSelectors.value),
                type: this._getInputValue(this.criteriaValueSelectors.type)
            };
        },

        /**
         * @inheritDoc
         */
        _triggerUpdate: function(newValue, oldValue) {
            if (!app.isEqualsLoosely(newValue.value, oldValue.value) || !app.isEqualsLoosely(newValue.type, oldValue.type)) {
                this.trigger('update');
            }
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            // synchronize choice selector with new value
            var menu = this.$('.choicefilter .dropdown-menu');
            menu.find('li a').each(function() {
                var item = $(this);
                if (item.data('value') == oldValue.type && item.parent().hasClass('active')) {
                    item.parent().removeClass('active');
                } else if (item.data('value') == newValue.type && !item.parent().hasClass('active')) {
                    item.parent().addClass('active');
                    item.closest('.AknDropdown').find('AknActionButton').html(item.html() + '<span class="AknActionButton-caret AknCaret"></span>');
                }
            });
            if (_.contains(['empty', 'not empty'], newValue.type)) {
                this.$(this.criteriaValueSelectors.value).hide();
            } else {
                this.$(this.criteriaValueSelectors.value).show();
            }

            TextFilter.prototype._onValueUpdated.apply(this, arguments);
        },

        /**
         * Open/close select dropdown
         *
         * @param {Event} e
         * @protected
         */
        _onClickChoiceValue: function(e) {
            var dropdown = $(e.currentTarget).closest('.AknDropdown');

            dropdown.find('li').each(function() {
                $(this).removeClass('active');
            });
            $(e.currentTarget).parent().addClass('active');
            dropdown.find('.name_input').val($(e.currentTarget).attr('data-value'));

            var filterContainer = $(e.currentTarget).closest('.AknFilterChoice');
            if ($(e.currentTarget).attr('data-value') === 'in') {
                this._enableListSelection();
            } else {
                this._disableListSelection();
            }
            if (_.contains(['empty', 'not empty'], $(e.currentTarget).attr('data-value'))) {
                filterContainer.find(this.criteriaValueSelectors.value).hide();
            } else {
                filterContainer.find(this.criteriaValueSelectors.value).show();
            }
            dropdown.find('.AknActionButton').html($(e.currentTarget).html() + '<span class="AknActionButton-caret AknCaret"></span>');
            e.preventDefault();
        },

        _enableListSelection: function() {
            initSelect2.init(this.$(this.criteriaValueSelectors.value), {
                multiple: true,
                tokenSeparators: [',', ' ', ';'],
                tags: [],
                width: '290px',
                formatNoMatches: function() { return ''; }
            });
        },

        _disableListSelection: function() {
            this.$(this.criteriaValueSelectors.value).select2('destroy');
        }
    });
});
