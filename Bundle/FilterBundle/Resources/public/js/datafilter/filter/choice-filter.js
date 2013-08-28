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
                            '<%= first = _.first(_.values(choices)) %>' +
                            '<span class="caret"></span>' +
                        '</button>' +
                        '<ul class="dropdown-menu">' +
                            '<% _.each(choices, function (hint, value) { %>' +
                                '<li><a class="choice_value" href="#" data-value="<%= value %>"><%= hint %></a></li>' +
                            '<% }); %>' +
                        '</ul>' +
                        '<input type="text" name="value" value="">' +
                        '<input class="name_input" type="hidden" name="<%= name %>" id="<%= name %>" value="<%= _.invert(choices)[first] %>"/>' +
                        '</div>' +
                    '</div>' +
                    '<button class="btn btn-primary filter-update" type="button"><%- __("Update") %></button>' +
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
        choices: {},

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
            var value = this._getDisplayValue();
            if (!value.value) {
                return this.defaultCriteriaHint;
            } else if (_.has(this.choices, value.type)) {
                return this.choices[value.type] + ' "' + value.value + '"'
            } else {
                return '"' + value.value + '"';
            }
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
