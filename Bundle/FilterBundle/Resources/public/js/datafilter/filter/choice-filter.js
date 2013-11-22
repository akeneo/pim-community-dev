/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/datafilter/text-filter'],
function($, _, __, TextFilter) {
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
                            '<%= first = _.first(choices).label %>' +
                            '<span class="caret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">' +
                            '<% _.each(choices, function (option) { %>' +
                                '<li><a class="choice_value" href="#" data-value="<%= option.value %>"><%= option.label %></a></li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<input type="text" name="value" value="">' +
                        '<input class="name_input" type="hidden" name="<%= name %>" id="<%= name %>" value="<%= _.first(choices).value %>"/>' +
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

        /** @property */
        choices: [],

        /**
         * Empty value object
         *
         * @property {Object}
         */
        emptyValue: {
            value: '',
            type: ''
        },

        /**
         * Initialize.
         *
         * @param {Object} options
         */
        initialize: function() {
            // temp code to keep backward compatible
            if ($.isPlainObject(this.choices)) {
                this.choices = _.map(this.choices, function(option, i) {
                    return {value: i.toString(), label: option};
                });
            }
            TextFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        _renderCriteria: function(el) {
            $(el).append(this.popupCriteriaTemplate({
                name: this.name,
                choices: this.choices
            }));
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
            if (newValue.value || oldValue.value) {
                this.trigger('update');
            }
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
