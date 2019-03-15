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
        'oro/translator',
        'oro/datagrid/pagination',
        'pim/template/datagrid/pagination',
        'oro/messenger'
    ], function(
        $,
        mediator,
        _,
        __,
        Pagination,
        template,
        Messenger
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

        /** @property */
        maxRescoreWindow: 10000,

        /**
         * @inheritDoc
         */
        initialize: function (options) {
            this.appendToGrid = options.appendToGrid;
            this.gridElement = options.gridElement;
            this.gridName = options.config.gridName;

            if (null === this.gridName || undefined === this.gridName) {
                throw Error('You must set the gridName in the form_extensions config for the oro/datagrid/pagination-input');
            }

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
            if (collection.inputName !== this.gridName) return;

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
                            className: 'AknActionButton--unclickable'
                        });
                    }
                    previousId = id;
                    handles.push({
                        label: id + 1,
                        title: 'No. ' + (id + 1),
                        className: currentPage === id ? 'active AknActionButton--highlight' : undefined
                    });
                });

                if (
                    state.totalRecords > this.maxRescoreWindow &&
                    (previousId + 1) * state.pageSize < this.maxRescoreWindow
                ) {
                    handles.push({
                        label: this.fastForwardHandleConfig.gap.label,
                        title: this.fastForwardHandleConfig.gap.label,
                        className: 'AknActionButton--unclickable'
                    });
                }
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
            const lastAccessiblePage = Math.floor(this.maxRescoreWindow / state.pageSize);
            const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
            let windowStart = currentPage - (this.windowSize - 1) / 2;
            windowStart = Math.max(Math.min(windowStart, lastPage - this.windowSize + 1), 0);
            const windowEnd = Math.min(windowStart + this.windowSize, lastPage, lastAccessiblePage);
            let ids = [];

            ids.push(state.firstPage - 1);
            for (let i = windowStart; i < windowEnd; i++) {
                ids.push(i);
            }

            if (state.totalRecords < this.maxRescoreWindow) {
                ids.push(lastPage);
            }

            return _.uniq(ids);
        },

        /**
         * {@inheritdoc}
         */
        onChangePage: function (e) {
            const label = $(e.target).text().trim();

            if (label === this.fastForwardHandleConfig.gap.label) {
                return false;
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

                const state = this.collection.state;
                const currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
                if ((currentPage + 1) === Math.floor(this.maxRescoreWindow / state.pageSize)) {
                    Messenger.notify(
                        'warning',
                        __('oro.datagrid.pagination.limit_warning', { limit: this.maxRescoreWindow }),
                    );
                }

                if (this.options.appendToGrid) {
                    this.gridElement.prepend(this.$el);
                }
            }

            return this;
        }
    });

    PaginationInput.init = function(gridContainer, gridName) {
        return new PaginationInput({ appendToGrid: true, gridElement: $(gridContainer).find('.grid-container'), config: {
            gridName
        } });
    };

    return PaginationInput;

});
