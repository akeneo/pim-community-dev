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
                let columns = this.entityPromises['columns']

                if (!columns) {
                    columns = $.getJSON(Routing.generate(this.options.urls.columns, { alias: alias }));
                    this.entityPromises['columns'] = columns;
                }

                return columns;
            },

            /**
             * Fetch default datagrid view for given alias of the current user
             *
             * @param {string} alias
             *
             * @return Promise
             */
            defaultUserView: function (alias) {
                let view = this.entityPromises['view']

                if (!view) {
                    view = $.getJSON(Routing.generate(this.options.urls.userDefaultView, { alias: alias }));
                    this.entityPromises['view'] = view
                }

                return view
            }
        });
    }
);
