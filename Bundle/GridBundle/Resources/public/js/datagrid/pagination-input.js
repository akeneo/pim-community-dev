/* global define */
define(['jquery', 'underscore', 'oro/grid/pagination', 'jquery.numeric'],
function($, _, Pagination) {
    'use strict';

    /**
     * Datagrid pagination with input field
     *
     * @export  oro/grid/pagination-input
     * @class   oro.grid.PaginationInput
     * @extends oro.grid.Pagination
     */
    return Pagination.extend({
        /** @property */
        template: _.template(
            '<label class="dib">Page:</label>' +
            '<ul class="icons-holder">' +
                '<% _.each(handles, function (handle) { %>' +
                    '<li <% if (handle.className || disabled) { %>class="<%= handle.className %> <% if (disabled) { %>disabled<% } %>"<% } %>>' +
                        '<% if (handle.type == "input") { %>' +
                            '<input type="text" value="<%= state.firstPage == 0 ? state.currentPage + 1 : state.currentPage  %>"' +
                                ' <% if (disabled) { %>disabled="disabled"<% } %>' +
                            '/>' +
                        '<% } else { %>' +
                            '<a href="#" <% if (handle.title) {%> title="<%= handle.title %>"<% } %>>' +
                                '<% if (handle.wrapClass) {%>' +
                                    '<i <% if (handle.wrapClass) { %>class="<%= handle.wrapClass %>"<% } %>>' +
                                        '<%= handle.label %>' +
                                    '</i>' +
                                '<% } else { %>' +
                                    '<%= handle.label %>' +
                                '<% } %>' +
                            '</a>' +
                        '<% } %>' +
                    '</li>' +
                '<% }); %>' +
            '</ul>' +
            '<label class="dib">of <%= state.totalPages ? state.totalPages : 1 %> | <%= state.totalRecords %> records</label>'
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
            Pagination.prototype.initialize.call(this, options);
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
            var collection = this.collection;
            var ffConfig = this.fastForwardHandleConfig;

            handles.push({
                type: 'input'
            });

            return Pagination.prototype.makeHandles.call(this, handles);
        },
        /**
         * Render pagination view and add validation for input with positive integer value
         */
        render: function() {
            Pagination.prototype.render.apply(this, arguments);
            this.$('input').numeric({ decimal: false, negative: false });
            return this;
        }
    });
});
