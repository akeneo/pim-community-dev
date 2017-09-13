/* global define */
define(['jquery', 'oro/mediator', 'underscore', 'oro/datagrid/pagination', 'jquery.numeric'],
function($, mediator, _, Pagination) {
    'use strict';

    /**
     * Datagrid pagination with input field
     *
     * @export  oro/datagrid/pagination-input
     * @class   oro.datagrid.PaginationInput
     * @extends oro.datagrid.Pagination
     */
    const PaginationInput = Pagination.extend({
        collection: {},
        /** @property */
        template: _.template(
            '<label class="AknGridToolbar-label"><%= _.__("oro.datagrid.pagination.label") %>:</label>' +
            '<div>' +
                '<% _.each(handles, function (handle) { %>' +
                    '<% if (handle.type == "input") { %>' +
                        '<input class="AknActionButton AknActionButton--input AknActionButton--glued <% if (handle.className || disabled) { %><%= handle.className %> <% if (disabled) { %>disabled<% } %><% } %>" type="text" value="<%= state.firstPage == 0 ? state.currentPage + 1 : state.currentPage %>" size="1"/>' +
                    '<% } else { %>' +
                        '<a class="AknActionButton AknActionButton--square AknActionButton--glued <% if (handle.className || disabled) { %><%= handle.className %> <% if (disabled) { %>disabled<% } %><% } %>" href="#" <% if (handle.title) {%> title="<%= handle.title %>"<% } %>>' +
                            '<% if (handle.wrapClass) {%>' +
                                '<i <% if (handle.wrapClass) { %>class="<%= handle.wrapClass %>"<% } %>>' +
                                    '<%= handle.label %>' +
                                '</i>' +
                            '<% } else { %>' +
                                '<%= handle.label %>' +
                            '<% } %>' +
                        '</a>' +
                    '<% } %>' +
                '<% }); %>' +
            '</div>' +
            '<label class="AknGridToolbar-label"><%= _.__("oro.datagrid.pagination.totalPages", {totalPages: state.totalPages || 1}) %> | <%= _.__("oro.datagrid.pagination.totalRecords", {totalRecords: state.totalRecords}) %></label>'
        ),

        /** @property */
        events: {
            "click a": "onChangePage",
            "blur input": "onChangePageByInput",
            "change input": "onChangePageByInput",
            'keyup input': function(e) {
                if (e.which == 13) {
                    // fix for IE 8, bacause change event is not fired when enter is pressed
                    this.onChangePageByInput(e);
                }
            }
        },

        /** @property */
        windowSize: 0,

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

        setupPagination(collection) {
            this.collection = collection;
            this.renderPagination();

            return Pagination.prototype.initialize.call(this, {
                collection: this.collection,
                enabled: true
            });
        },

        /**
         * Apply change of pagination page input
         *
         * @param {Event} e
         */
        onChangePageByInput: function(e) {
            e.preventDefault();

            var pageIndex = parseInt($(e.target).val());
            var collection = this.collection;
            var state = collection.state;

            if (_.isNaN(pageIndex)) {
                $(e.target).val(state.currentPage);
                return;
            }

            pageIndex = state.firstPage == 0 ? pageIndex - 1  : pageIndex;
            if (pageIndex < state.firstPage) {
                pageIndex = state.firstPage;
                $(e.target).val(state.firstPage == 0 ? state.firstPage + 1 : state.firstPage);
            } else if (state.lastPage <= pageIndex) {
                pageIndex = state.lastPage;
                $(e.target).val(state.firstPage == 0 ? state.lastPage + 1 : state.lastPage);
            }

            if (state.currentPage !== pageIndex) {
                collection.getPage(pageIndex);
            }
        },

        /**
         * Internal method to create a list of page handle objects for the template
         * to render them.
         *
         * @return Array.<Object> an array of page handle objects hashes
         */
        makeHandles: function () {
            var handles = [];

            handles.push({
                type: 'input'
            });

            return Pagination.prototype.makeHandles.call(this, handles);
        },
        /**
         * Render pagination view and add validation for input with positive integer value
         */
        renderPagination: function() {
            Pagination.prototype.renderPagination.apply(this, arguments);
            this.$('input').numeric({ decimal: false, negative: false });

            if (this.options.appendToGrid) {
                this.gridElement.prepend(this.$el);
            }

            return this;
        }
    });

    PaginationInput.init = function(gridContainer) {
        return new PaginationInput({ appendToGrid: true, gridElement: $(gridContainer).find('.grid-container') });
    };

    return PaginationInput;

});
