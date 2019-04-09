/* global define */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/datafilter/abstract-filter',
        'pim/template/datagrid/filter/text-filter'
    ], function(
        $,
        _,
        __,
        AbstractFilter,
        criteriaTemplate
    ) {
    'use strict';

    /**
     * Text grid filter.
     *
     * Triggers events:
     *  - "disable" when filter is disabled
     *  - "update" when filter criteria is changed
     *
     * @export  oro/datafilter/text-filter
     * @class   oro.datafilter.TextFilter
     * @extends oro.datafilter.AbstractFilter
     */
    return AbstractFilter.extend({
        outsideEventListener: null,

        /** @property */
        template: _.template(
            '<div class="AknFilterBox-filter filter-criteria-selector oro-drop-opener">' +
                '<% if (showLabel) { %>' +
                    '<span class="AknFilterBox-filterLabel"><%= label %></span>' +
                '<% } %>' +
                '<span class="AknFilterBox-filterCriteria AknFilterBox-filterCriteria--limited filter-criteria-hint"><%= criteriaHint %></span>' +
                '<span class="AknFilterBox-filterCaret"></span>' +
            '</div>' +
            '<div class="filter-criteria dropdown-menu" />' +
            '<% if (canDisable) { %><div class="AknFilterBox-disableFilter AknIconButton AknIconButton--remove disable-filter"></div><% } %>'
        ),

        /**
         * Template for filter criteria
         *
         * @property
         */
        popupCriteriaTemplate: _.template(criteriaTemplate),

        /**
         * @property {Boolean}
         */
        popupCriteriaShowed: false,

        /**
         * Selector to element of criteria hint
         *
         * @property {String}
         */
        criteriaHintSelector: '.filter-criteria-hint',

        /**
         * Selector to criteria popup container
         *
         * @property {String}
         */
        criteriaSelector: '.filter-criteria',

        /**
         * Selectors for filter criteria elements
         *
         * @property {Object}
         */
        criteriaValueSelectors: {
            value: 'input[name="value"]',
            nested: {
                end: 'input'
            }
        },

        operatorChoices: [],

        /**
         * View events
         *
         * @property {Object}
         */
        events: {
            'keyup input': '_onReadCriteriaInputKey',
            'keydown [type="text"]': '_preventEnterProcessing',
            'click .filter-update': '_onClickUpdateCriteria',
            'click .filter-criteria-selector': '_onClickCriteriaSelector',
            'click .filter-criteria .filter-criteria-hide': '_onClickCloseCriteria',
            'click .disable-filter': '_onClickDisableFilter'
        },

        /**
         * Initialize.
         *
         * @param {Object} options
         */
        initialize: function() {
            // init empty value object if it was not initialized so far
            if (_.isUndefined(this.emptyValue)) {
                this.emptyValue = {
                    value: ''
                };
            }

            AbstractFilter.prototype.initialize.apply(this, arguments);
        },

        /**
         * Makes sure the criteria popup dialog is closed
         */
        ensurePopupCriteriaClosed: function () {
            if (this.popupCriteriaShowed) {
                this._hideCriteria();
                this.setValue(this._formatRawValue(this._readDOMValue()));
            }
        },

        /**
         * Handle key press on criteria input elements
         *
         * @param {Event} e
         * @protected
         */
        _onReadCriteriaInputKey: function(e) {
            if (e.which == 13) {
                this._hideCriteria();
                this.setValue(this._formatRawValue(this._readDOMValue()));
            }
        },

        /**
         * Handle click on criteria update button
         *
         * @param {Event} e
         * @private
         */
        _onClickUpdateCriteria: function(e) {
            this._hideCriteria();
            this.setValue(this._formatRawValue(this._readDOMValue()));
        },

        /**
         * Handle click on criteria selector
         *
         * @param {Event} e
         * @protected
         */
        _onClickCriteriaSelector: function(e) {
            e.stopPropagation();
            $('body').trigger('click');
            if (!this.popupCriteriaShowed) {
                this._showCriteria();
            } else {
                this._hideCriteria();
            }
        },

        /**
         * Handle click on criteria close button
         *
         * @private
         */
        _onClickCloseCriteria: function() {
            this._hideCriteria();
            this._updateDOMValue();
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
         * Render filter view
         *
         * @return {*}
         */
        render: function () {
            AbstractFilter.prototype.render.apply(this, arguments);

            this.$el.empty();
            this.$el.append(
                this.template({
                    label: this.label,
                    showLabel: this.showLabel,
                    criteriaHint: this._getCriteriaHint(),
                    nullLink: this.nullLink,
                    canDisable: this.canDisable
                })
            );
            this._renderCriteria(this.$(this.criteriaSelector));

            return this;
        },

        /**
         * Render filter criteria popup
         *
         * @param {Object} el
         * @protected
         * @return {*}
         */
        _renderCriteria: function(el) {
            $(el).append(
                this.popupCriteriaTemplate({
                    label: this.label,
                    operatorChoices: this._getOperatorChoices(),
                    selectedOperator: this.emptyValue.type,
                    emptyChoice: this.emptyChoice,
                    selectedOperatorLabel: this._getOperatorChoices()[this.emptyValue.type],
                    operatorLabel: __('pim_common.operator'),
                    updateLabel: __('pim_common.update')
                })
            );
            return this;
        },

        /**
         * Returns a list of operator { key: label }
         *
         * @returns {Object}
         */
        _getOperatorChoices() {
            return this.operatorChoices;
        },

        /**
         * Unsubscribe from click on body event
         *
         * @return {*}
         */
        remove: function() {
            $('body').off('click', this._clickOutsideCriteriaCallback);
            AbstractFilter.prototype.remove.call(this);
            return this;
        },

        /**
         * Show criteria popup
         *
         * @protected
         */
        _showCriteria: function() {
            this.$(this.criteriaSelector).show();
            this._updateCriteriaSelectorPosition();
            this._focusCriteria();
            this._setButtonPressed(this.$(this.criteriaSelector), true);
            setTimeout(_.bind(function() {
                this.popupCriteriaShowed = true;
            }, this), 100);

            this.outsideEventListener = this.outsideClickListener.bind(this)
            document.addEventListener('click', this.outsideEventListener);
        },

        /**
         * Closes the criteria if the user clicks on the rest of the document.
         *
         * The condition is a combination of:
         * - The click is outside of the criteria Selector,
         * - The click is in the app (this is due to select2 it removes its full position layer, leading to a
         *   event.target == body),
         * - The popup is open.
         *
         * @param {Event} event
         */
        outsideClickListener(event) {
            if (!$(event.target).closest(this.criteriaSelector).length &&
                (
                    $(event.target).closest('.app').length ||
                    $(event.target).closest('.modal--fullPage').length
                ) && this.popupCriteriaShowed) {
                this._hideCriteria();
                this.setValue(this._formatRawValue(this._readDOMValue()));

                document.removeEventListener('mousedown', this.outsideEventListener);
            }
        },

        /**
         * Hide criteria popup
         *
         * @protected
         */
        _hideCriteria: function() {
            this.$(this.criteriaSelector).hide();
            this._setButtonPressed(this.$(this.criteriaSelector), false);
            setTimeout(_.bind(function() {
                this.popupCriteriaShowed = false;
            }, this), 100);
        },

        /**
         * Focus filter criteria input
         *
         * @protected
         */
        _focusCriteria: function() {
            this.$(this.criteriaSelector + ' input').trigger('focus').trigger('select');
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.criteriaValueSelectors.value, value.value);
            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {
                value: this._getInputValue(this.criteriaValueSelectors.value)
            };
        },

        /**
         * @inheritDoc
         */
        _onValueUpdated: function(newValue, oldValue) {
            AbstractFilter.prototype._onValueUpdated.apply(this, arguments);
            this._updateCriteriaHint();
        },

        /**
         * Updates criteria hint element with actual criteria hint value
         *
         * @protected
         * @return {*}
         */
        _updateCriteriaHint: function() {
            this.$(this.criteriaHintSelector).html(_.escape(this._getCriteriaHint()));
            return this;
        },

        /**
         * Get criteria hint value
         *
         * @return {String}
         * @protected
         */
        _getCriteriaHint: function() {
            var value = (arguments.length > 0) ? this._getDisplayValue(arguments[0]) : this._getDisplayValue();
            return value.value ? '"' + value.value + '"': this.placeholder;
        },

        /**
         * Enables text input
         *
         * @protected
         */
        _enableInput: function() {
            this.$(this.criteriaValueSelectors.value).show();
        },

        /**
         * Disables text input
         *
         * @protected
         */
        _disableInput: function() {
            this.$(this.criteriaValueSelectors.value).hide();
        }
    });
});
