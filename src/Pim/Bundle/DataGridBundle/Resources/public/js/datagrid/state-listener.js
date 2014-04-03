define(
    ['underscore', 'oro/mediator', 'oro/datagrid/abstract-listener', 'pim/datagrid/state'],
    function(_, mediator, AbstractListener, DatagridState) {
        'use strict';

        /**
         * Datagrid state listener
         */
        var StateListener = AbstractListener.extend({
            gridName: null,
            $gridContainer: null,

            initialize: function (options) {
                if (!_.has(options, 'gridName')) {
                    throw new Error('Grid name not specified');
                }
                if (!_.has(options, '$gridContainer')) {
                    throw new Error('Grid container not specified');
                }

                this.gridName       = options.gridName;
                this.$gridContainer = options.$gridContainer;

                this.subscribe();
            },

            subscribe: function () {
                mediator.once('datagrid_collection_set_after', this.afterCollectionSet, this);
                mediator.on('grid_load:complete', this.saveGridState, this);

                this.$gridContainer.on('preExecute:reset:' + this.gridName, this.onGridReset.bind(this));

                mediator.once('hash_navigation_request:start', this.unsubscribe, this);
            },

            unsubscribe: function () {
                mediator.off('grid_load:complete', this.saveGridState, this);
            },

            afterCollectionSet: function () {
                mediator.once(
                    'datagrid_filters:rendered',
                    function (collection) {
                        collection.trigger('updateState', collection);

                        // We have to use a timeout here because the toolbar is hidden right after triggering this event
                        setTimeout(_.bind(function() {
                            this.$gridContainer.find('div.toolbar, div.filter-box').show();
                        }, this), 20);
                    }, this
                );
            },

            saveGridState: function (collection) {
                if (collection.inputName === this.gridName) {
                    var $filterBox = this.$gridContainer.find('.filter-box');
                    if ($filterBox.length && !$filterBox.is(':visible')) {
                        $filterBox.show();
                    }

                    var encodedStateData = collection.encodeStateData(collection.state);
                    DatagridState.set(this.gridName, 'filters', encodedStateData);
                }
            },

            onGridReset: function (e, action) {
                action.collection.initialState.filters = {};
            }
        });

        StateListener.init = function ($gridContainer, gridName) {
            new StateListener({ $gridContainer: $gridContainer, gridName: gridName });
        };

        return StateListener;
    }
);
