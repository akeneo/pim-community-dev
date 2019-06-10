'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'oro/datafilter/abstract-filter',
        'pim/template/datagrid/filter/search-filter'
    ], function (
        $,
        _,
        __,
        AbstractFilter,
        template
    ) {
        return AbstractFilter.extend({
            inputValueSelector: 'input[name="value"]',

            events: {
                'keydown input[name="value"]': 'runTimeout',
                'keypress input[name="value"]': 'runTimeout',
                'focusin input[name="value"]': 'disableReadonly',
                'focusout input[name="value"]': 'enableReadonly'
            },

            emptyValue: {
                value: ''
            },

            timer: null,

            isSearch: true,

            timeoutDelay: 500,

            className: 'AknFilterBox-searchContainer filter-item search-filter',

            template: _.template(template),

            /**
             * {@inheritDoc}
             */
            render: function () {
                this.$el.html(
                    this.template({
                        label: __('pim_datagrid.search', {label: this.label})
                    })
                );

                this.enableReadonly();
            },

            /**
             * There is a bug in the autocomplete="off" attribute in several browser. This attribute is not taken in
             * account in the case of autocomplete username/password fields.
             * In some screens, the search input is mixed up with username field, and the panel for password
             * autocomplete opens.
             * Another bug is if you select a password combination in the User creation modal, it will fill the search
             * input instead of the username field in the modal.
             * The solution is to set this field as readonly if the user has no focus on it.
             *
             * @see https://bugs.chromium.org/p/chromium/issues/detail?id=468153
             * @see https://stackoverflow.com/questions/12374442/chrome-ignores-autocomplete-off
             */
            disableReadonly: function () {
                this.$el.find(this.inputValueSelector).attr('readonly', null);
            },

            enableReadonly: function () {
                this.$el.find(this.inputValueSelector).attr('readonly', true);
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function (value) {
                this._setInputValue(this.inputValueSelector, value.value);

                return this;
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function () {
                return {
                    value: this._getInputValue(this.inputValueSelector)
                };
            },

            /**
             * Runs a timer to wait some time. When the time is done, it execute the search.
             * If the user types another time in the search box, it resets the timer and restart one.
             *
             * @param {Event} event
             */
            runTimeout: function (event) {
                if (null !== this.timer) {
                    clearTimeout(this.timer);
                }

                if (13 === event.keyCode) { // Enter key
                    this.doSearch();
                } else {
                    this.timer = setTimeout(
                        this.doSearch.bind(this),
                        this.timeoutDelay
                    );
                }
            },

            /**
             * Executes the search by setting the value.
             */
            doSearch: function () {
                this.setValue(this._readDOMValue());
            }
        });
    }
);
