/* global define */
define(['oro/translator', 'oro/datafilter/abstract-filter'],
    function(__, AbstractFilter) {
        'use strict';

        /**
         * None filter: an empty filter implements 'null object' pattern
         *
         * @export  oro/datafilter/none-filter
         * @class   oro.datafilter.NoneFilter
         * @extends oro.datafilter.AbstractFilter
         */
        return AbstractFilter.extend({
            /**
             * Filter template
             *
             * @property
             */
            template: _.template('<div class="btn"><%= placeholder %></div>'),

            /**
             * Initialize.
             *
             * @param {Object} options
             */
            initialize: function() {
                AbstractFilter.prototype.initialize.apply(this, arguments);
            },

            /**
             * Render filter template
             *
             * @return {*}
             */
            render: function () {
                this.$el.empty();
                this.$el.append(this.template({placeholder: __('Choice a criterion')}));
                return this;
            },

            /**
             * @inheritDoc
             */
            _writeDOMValue: function(value) {
                return this;
            },

            /**
             * @inheritDoc
             */
            _readDOMValue: function() {
                return {};
            }
        });
    });
