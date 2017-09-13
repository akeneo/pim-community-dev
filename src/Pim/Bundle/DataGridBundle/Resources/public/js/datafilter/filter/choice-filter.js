/* global define */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/app',
        'oro/datafilter/text-filter',
        'pim/initselect2',
        'jquery.select2'
    ], function(
        $,
        _,
        __,
        app,
        TextFilter,
        initSelect2,
    ) {
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
         * Selectors for filter criteria elements
         *
         * @property {Object}
         */
        criteriaValueSelectors: {
            value: 'input[name="value"]',
            type: 'input[type="hidden"]'
        },

        emptyChoice: true,

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
            'click .AknDropdown-menuLink': '_onSelectOperator',
            'click .disable-filter': '_onClickDisableFilter'
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
            this._highlightOperator(value.type);

            return this;
        },

        /**
         * Highlights the current operator
         *
         * @param operator
         */
        _highlightOperator(operator) {
            this.$el.find('.operator .AknDropdown-menuLink')
                .removeClass('AknDropdown-menuLink--active')
                .removeClass('active');

            const currentOperatorChoice = this.$el.find('.operator .operator_choice[data-value="' + operator + '"]');
            currentOperatorChoice.parent()
                .addClass('AknDropdown-menuLink--active')
                .addClass('active');

            this.$el.find('.operator .AknActionButton-highlight').html(currentOperatorChoice.text());
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            const operator = this.emptyChoice ? this.$('.active .operator_choice').data('value') : 'in';

            return {
                value: _.contains(['empty', 'not empty'], operator) ? {} : this._getInputValue(this.criteriaValueSelectors.value),
                type: operator
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
         * Updates the select classes and hide/show sub-elements
         *
         * @param {Event} e
         * @protected
         */
        _onSelectOperator: function(e) {
            const value = $(e.currentTarget).find('.operator_choice').attr('data-value');
            this._highlightOperator(value);

            if (value === 'in') {
                this._enableListSelection();
            } else {
                this._disableListSelection();
            }
            if (_.contains(['empty', 'not empty'], value)) {
                this._disableInput();
            } else {
                this._enableInput();
            }

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
            this.$(this.criteriaValueSelectors.value).addClass('AknTextField--select2');
        },

        _disableListSelection: function() {
            this.$(this.criteriaValueSelectors.value).select2('destroy').removeClass('AknTextField--select2');
        },

        /**
         * {@inheritdoc}
         */
        _getOperatorChoices() {
            let formattedChoices = {};
            _.each(this.choices, function (choice) {
                formattedChoices[choice.value] = choice.label;
            });

            return formattedChoices;
        },
    });
});
