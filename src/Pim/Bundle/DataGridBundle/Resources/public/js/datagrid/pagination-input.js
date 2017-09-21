/* global define */

/**
 * Datagrid pagination with input field
 *
 * @export  oro/datagrid/pagination-input
 * @class   oro.datagrid.PaginationInput
 * @extends oro.datagrid.Pagination
 */
define(
    [
        'jquery',
        'oro/mediator',
        'underscore',
        'oro/datagrid/pagination',
        'pim/template/datagrid/pagination',
        'jquery.numeric'
    ], function(
        $,
        mediator,
        _,
        Pagination,
        template
    ) {
    'use strict';

    const PaginationInput = Pagination.extend({
        collection: {},

        /** @property */
        template: _.template(template),

        /** @property */
        windowSize: 3,

        /** @property */
        fastForwardHandleConfig: {
            gap: {
                label: '...'
            }
        },

        /**
         * @inheritDoc
         */
        initialize: function (options) {
            this.appendToGrid = options.appendToGrid;
            this.gridElement = options.gridElement;

            if (this.appendToGrid) {
                mediator.on('datagrid_collection_set_after', this.setupPagination.bind(this));
            }

            mediator.once('grid_load:start', this.setupPagination.bind(this));
            mediator.on('grid_load:complete', this.setupPagination.bind(this));
        },

        /**
         * Initialize the pagination with the collection
         *
         * @param collection
         */
        setupPagination(collection) {
            this.collection = collection;
            this.renderPagination();

            return Pagination.prototype.initialize.call(this, {
                collection: this.collection,
                enabled: true
            });
        },

        /**
         * {@inheritdoc}
         */
        makeHandles: function () {
            let handles = [];

            const state = this.collection.state;
            const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
            const pageIds = this.getPages();

            if (this.collection.mode !== 'infinite') {
                let previousId = _.first(pageIds);
                pageIds.forEach((id) => {
                    if (id - previousId > 1) {
                        handles.push({
                            label: this.fastForwardHandleConfig.gap.label,
                            title: this.fastForwardHandleConfig.gap.label,
                            className: 'AknActionButton--disabled'
                        });
                    }
                    previousId = id;
                    handles.push({
                        label: id + 1,
                        title: 'No. ' + (id + 1),
                        className: currentPage === id ? 'active AknActionButton--highlight' : undefined
                    });
                });
            }

            return handles;
        },

        /**
         * Returns the list of pages to display
         */
        getPages() {
            const collection = this.collection;
            const state = collection.state;

            let lastPage = state.lastPage ? state.lastPage : state.firstPage;
            lastPage = state.firstPage === 0 ? lastPage : lastPage - 1;
            const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
            let windowStart = currentPage - (this.windowSize - 1) / 2;
            windowStart = Math.max(Math.min(windowStart, lastPage - this.windowSize + 1), 0);
            const windowEnd = Math.min(windowStart + this.windowSize, lastPage);
            let ids = [];

            ids.push(state.firstPage - 1);
            for (let i = windowStart; i < windowEnd; i++) {
                ids.push(i);
            }
            ids.push(lastPage);

            return _.uniq(ids);
        },

        /**
         * {@inheritdoc}
         */
        onChangePage: function (e) {
            const label = $(e.target).text();

            if (label === this.fastForwardHandleConfig.gap.label) {
                return;
            }

            return Pagination.prototype.onChangePage.apply(this, arguments);
        },

        /**
         * Render pagination view and add validation for input with positive integer value
         */
        renderPagination: function() {
            if (this.getPages().length <= 1) {
                this.$el.empty();
            } else {
                Pagination.prototype.renderPagination.apply(this, arguments);

                if (this.options.appendToGrid) {
                    this.gridElement.prepend(this.$el);
                }
            }

            return this;
        }
    });

    PaginationInput.init = function(gridContainer) {
        return new PaginationInput({ appendToGrid: true, gridElement: $(gridContainer).find('.grid-container') });
    };

    return PaginationInput;

});
