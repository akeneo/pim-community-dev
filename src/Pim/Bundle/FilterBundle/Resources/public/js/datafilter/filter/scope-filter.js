define(
    ['underscore', 'oro/datafilter/select-filter'],
    function (_, SelectFilter) {
        'use strict';

        /**
         * Scope filter
         *
         * @author    Romain Monceau <romain@akeneo.com>
         * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
         * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
         *
         * @export  pim/datafilter/scope-filter
         * @class   pim.datafilter.ScopeFilter
         * @extends oro.datafilter.SelectFilter
         */
        return SelectFilter.extend({
            /**
             * @override
             * @property {Boolean}
             * @see Oro.Filter.SelectFilter
             */
            contextSearch: false,

            /**
             * @override
             * @property {Boolean}
             * @see Oro.Filter.SelectFilter
             */
            populateDefault: false,

            /**
             * @inheritDoc
             */
            disable: function () {
                return this;
            },

            /**
             * @inheritDoc
             */
            hide: function () {
                return this;
            },

            /**
             * Filter template
             *
             * @override
             * @property
             * @see Oro.Filter.SelectFilter
             */
            template: _.template(
                '<div class="btn filter-select filter-criteria-selector scope-filter">' +
                    '<i class="icon-eye-open" title="<%= label %>"></i>' +
                    '<select>' +
                        '<% _.each(options, function (option) { %>' +
                            '<option value="<%= option.value %>"><%= option.label %></option>' +
                        '<% }); %>' +
                    '</select>' +
                '</div>'
            )
        });
    }
);
