define(
    ['underscore', 'oro/mediator', 'oro/datagrid/abstract-listener', 'oro/pageable-collection'],
    function(_, mediator, AbstractListener, PageableCollection) {
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

                if (typeof Storage !== 'undefined' && sessionStorage) {
                    this.subscribe();
                }
            },

            subscribe: function () {
                mediator.once('datagrid_collection_set_after', this.afterCollectionSet, this)
                mediator.on('grid_load:complete', this.saveGridState, this);

                this.$gridContainer.on('preExecute:reset:' + this.gridName, this.onGridReset.bind(this));

                mediator.once('hash_navigation_request:start', this.unsubscribe, this);
            },

            unsubscribe: function () {
                mediator.off('grid_load:complete', this.saveGridState, this);
            },

            afterCollectionSet: function (collection) {
                mediator.once(
                    'datagrid_filters:rendered',
                    function (collection) {
                        collection.trigger('updateState', collection);
                    }
                );

                this.$gridContainer.find('.no-data').hide();

                collection.fetch();
            },

            saveGridState: function (collection) {
                if (collection.inputName === this.gridName) {
                    var $filterBox = $('#grid-' + this.gridName).find('.filter-box');
                    if ($filterBox.length && !$filterBox.is(':visible')) {
                        $filterBox.show();
                    }

                    var encodedStateData = collection.encodeStateData(collection.state);
                    sessionStorage.setItem(this.gridName, encodedStateData);
                }
            },

            onGridReset: function (e, action) {
                action.collection.initialState.filters = {};
            }
        });

        StateListener.init = function ($gridContainer, gridName) {
            new StateListener({ $gridContainer: $gridContainer, gridName: gridName });
        };

        StateListener.prepareGrid = function (gridName) {
            if (typeof Storage !== 'undefined' && sessionStorage) {
                var state = sessionStorage.getItem(gridName);

                if (state) {
                    var $gridContainer = $('#grid-' + gridName);
                    var storedState    = new PageableCollection().decodeStateData(state);

                    $gridContainer.data('metadata').state = storedState;
                }
            }
        };

        return StateListener;
    }
);
