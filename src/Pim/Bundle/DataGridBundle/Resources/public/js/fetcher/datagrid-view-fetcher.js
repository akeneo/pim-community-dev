'use strict';

/**
 * Datagrid View Fetcher.
 * We override the default fetcher to add additional methods
 * to fetch default columns & default user datagrid view.
 */
define(
    [
        'jquery',
        'routing',
        'pim/base-fetcher'
    ],
    function (
        $,
        Routing,
        BaseFetcher
    ) {
        return BaseFetcher.extend({
            /**
             * Fetch default columns for grid with given alias
             *
             * @param {string} alias
             *
             * @return Promise
             */
            defaultColumns: function (alias) {
                return $.getJSON(Routing.generate(this.options.urls.columns, { alias: alias }));
            },

            /**
             * Fetch default datagrid view for given alias of the current user
             *
             * @param {string} alias
             *
             * @return Promise
             */
            defaultUserView: function (alias) {
                return $.getJSON(Routing.generate(this.options.urls.userDefaultView, { alias: alias }));
            }
        });
    }
);
