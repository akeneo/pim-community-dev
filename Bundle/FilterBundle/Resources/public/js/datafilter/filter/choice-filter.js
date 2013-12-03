/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/app', 'oro/datafilter/text-filter'],
function($, _, __, app, TextFilter) {
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
            '<div class="choicefilter">' +
                '<div class="input-prepend">' +
                    '<div class="btn-group">' +
                        '<button class="btn dropdown-toggle" data-toggle="dropdown">' +
                            '<%= selectedChoiceLabel %>' +
                            '<span class="caret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">' +
                            '<% _.each(choices, function (option) { %>' +
                                '<li<% if (selectedChoice == option.value) { %> class="active"<% } %>><a class="choice_value" href="#" data-value="<%= option.value %>"><%= option.label %></a></li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<input type="text" name="value" value="">' +
                        '<input class="name_input" type="hidden" name="<%= name %>" id="<%= name %>" value="<%= selectedChoice %>"/>' +
                        '</div>' +
                    '</div>' +
                    '<button class="btn btn-primary filter-update" type="button"><%- _.__("Update") %></button>' +
                '</div>' +
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
            if (!app.isEqualsLoosely(newValue.value, oldValue.value)) {
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
                    menu.parent().find('button').html(item.html() + '<span class="caret"></span>');
                }
            });

            TextFilter.prototype._onValueUpdated.apply(this, arguments);
        },

        /**
         * Open/close select dropdown
         *
         * @param {Event} e
         * @protected
         */
        _onClickChoiceValue: function(e) {
            $(e.currentTarget).parent().parent().find('li').each(function() {
                $(this).removeClass('active');
            });
            $(e.currentTarget).parent().addClass('active');
            var parentDiv = $(e.currentTarget).parent().parent().parent();
            parentDiv.find('.name_input').val($(e.currentTarget).attr('data-value'));
            parentDiv.find('button').html($(e.currentTarget).html() + '<span class="caret"></span>');
            e.preventDefault();
        }
    });
});
