/* global define */
define(['jquery', 'underscore', 'oro/translator', 'oro/datafilter/abstract-filter'],
function($, _, __, AbstractFilter) {
    'use strict';

    return AbstractFilter.extend({
        inputValueSelector: 'input[name="value"]',

        events: {
            'keydown input[name="value"]': 'keyPress',
            'keypress input[name="value"]': 'keyPress'
        },

        emptyValue: {
            value: ''
        },

        timer: null,

        timerDelay: 500,

        className: 'AknSearch-inputContainer filter-item',

        render: function () {
            this.$el.html(
                '<input class="AknSearch-input" autocomplete="off" type="text" name="value" value="" placeholder="Search">'
            );
        },

        /**
         * @inheritDoc
         */
        _writeDOMValue: function(value) {
            this._setInputValue(this.inputValueSelector, value.value);
            return this;
        },

        /**
         * @inheritDoc
         */
        _readDOMValue: function() {
            return {
                value: this._getInputValue(this.inputValueSelector)
            };
        },

        keyPress: function(event) {
            if (null !== this.timer) {
                clearTimeout(this.timer);
            }

            if (13 === event.keyCode) {
                // Enter key
                this.doSearch();
            } else {
                this.timer = setTimeout(this.doSearch.bind(this), this.timerDelay);
            }
        },

        doSearch: function() {
            this.setValue(this._readDOMValue());
        }
    });
});
