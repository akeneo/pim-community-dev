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
                'keydown input[name=value]': 'runTimeout',
                'keypress input[name=value]': 'runTimeout'
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
                        label: __('pim_datagrid.search', {label: __('pim_common.' + this.label)})
                    })
                );

                this.delegateEvents();
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
            },

            /**
             * Appends filter to a grid
             * If .search-zone is not in the element that is to say it could be somewhere in the page.
             */
            moveFilter: function(collection, element) {
                if (element.$('.search-zone').length !== 0) {
                    element.$('.search-zone')
                    .append(this.$el.get(0));
                } else if ($('.edit-form .search-zone').length !== 0) {
                    $('.edit-form .search-zone')
                        .empty()
                        .append(this.$el.get(0));
                }
            }
        });
    }
);
