define(
    ['underscore', 'oro/mediator', 'oro/datagrid/abstract-listener'],
    function(_, mediator, AbstractListener) {
        'use strict';

        /**
         * Datagrid state listener
         */
        var StateListener = AbstractListener.extend({
            gridName: null,

            initialize: function (options) {
                if (!_.has(options, 'gridName')) {
                    throw new Error('Grid name not specified');
                }
                this.gridName = options.gridName;

                if (typeof Storage !== 'undefined' && sessionStorage) {
                    this.subscribe();
                }
            },

            subscribe: function () {
                mediator.on('datagrid_collection_set_after', this.restoreGridState, this)
                mediator.on('grid_load:complete', this.saveGridState, this);

                mediator.once('hash_navigation_request:start', this.unsubscribe, this);
            },

            unsubscribe: function () {
                mediator.off('datagrid_collection_set_after', this.restoreGridState, this);
                mediator.off('grid_load:complete', this.saveGridState, this);
            },

            restoreGridState: function (collection) {
                if (collection.inputName === this.gridName) {
                    var state = sessionStorage.getItem(this.gridName);

                    if (state) {
                        collection.updateState(collection.decodeStateData(state));
                        collection.fetch();
                    }
                }
            },

            saveGridState: function (collection) {
                if (collection.inputName === this.gridName) {
                    var encodedStateData = collection.encodeStateData(collection.state);
                    sessionStorage.setItem(this.gridName, encodedStateData);
                }
            }
        });

        StateListener.init = function ($gridContainer, gridName) {
            new StateListener(_.extend({ gridName: gridName }));
        };

        return StateListener;
    }
);
