'use strict';

/**
 * Override of the module of the datagrid View Selector.
 * We override this module to initialize the selector on projects if the user has
 * project to work on.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'jquery',
        'pim/grid/view-selector/selector',
        'pim/fetcher-registry'
    ],
    function (
        $,
        ViewSelector,
        FetcherRegistry
    ) {
        return ViewSelector.extend({

            /**
             * {@inheritdoc}
             *
             * We define the default project view type if the current user has some project to work on.
             * If the user doesn't have project, or if the server fails to answer, we fallback to view type.
             */
            initializeViewTypes: function () {
                var deferred = $.Deferred();
                var searchParameters = this.getSelectSearchParameters('', 1);

                FetcherRegistry
                    .getFetcher('datagrid-project')
                    .search(searchParameters)
                    .then(function (projects) {
                        if (projects.length > 0) {
                            this.currentViewType = 'project';
                        } else {
                            this.currentViewType = 'view';
                        }

                        deferred.resolve();
                    }.bind(this));

                return deferred.promise();
            }
        });
    }
);
