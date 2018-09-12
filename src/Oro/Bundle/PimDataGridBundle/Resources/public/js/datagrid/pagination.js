/* global define */
define(['jquery', 'underscore', 'backbone', 'pim/form'],
function($, _, Backbone, BaseForm) {
    'use strict';

    /**
     * Datagrid pagination widget
     *
     * @export  oro/datagrid/pagination
     * @class   oro.datagrid.Pagination
     * @extends BaseForm
     */
    return BaseForm.extend({
        /** @property */
        tagName: 'div',

        /** @property */
        className: 'AknGridToolbar-center',

        /** @property */
        windowSize: 10,

        /** @property */
        enabled: true,

        /** @property */
        hidden: false,

        /** @property */
        template: _.template(
            '<label>Page:</label>' +
            '<ul class="AknPagination-icons">' +
                '<% _.each(handles, function (handle) { %>' +
                    '<li class="AknPagination-item <% if (handle.className) { %><%= handle.className %><% } %>">' +
                        '<a href="#" class="AknPagination-link" <% if (handle.title) {%> title="<%= handle.title %>"<% } %>>' +
                            '<% if (handle.wrapClass) {%>' +
                                '<i <% if (handle.wrapClass) { %>class="<%= handle.wrapClass %>"<% } %>>' +
                                    '<%= handle.label %>' +
                                '</i>' +
                            '<% } else { %>' +
                                '<%= handle.label %>' +
                            '<% } %>' +
                        '</a>' +
                    '</li>' +
                '<% }); %>' +
            '</ul>' +
            '<label>of <%= state.totalPages ? state.totalPages : 1 %> | <%= state.totalRecords %> records</label>'
        ),

        /** @property */
        events: {
            "click a": "onChangePage"
        },

        /** @property */
        fastForwardHandleConfig: {
            prev: {
                label: '',
                wrapClass: 'icon-chevron-left hide-text'
            },
            next: {
                label: '',
                wrapClass: 'icon-chevron-right hide-text'
            }
        },

        /**
         * Initializer.
         *
         * @param {Object} options
         * @param {Backbone.Collection} options.collection
         * @param {Object} options.fastForwardHandleConfig
         * @param {Number} options.windowSize
         */
        initialize: function (options) {
            options = options || {};

            if (!options.collection) {
                throw new TypeError("'collection' is required");
            }

            this.collection = options.collection;
            this.listenTo(this.collection, "add", this.render);
            this.listenTo(this.collection, "remove", this.render);
            this.listenTo(this.collection, "reset", this.render);

            this.hidden = options.hide == true;

            BaseForm.prototype.initialize.call(this, options);
        },

        /**
         * Disable pagination
         *
         * @return {*}
         */
        disable: function() {
            this.enabled = false;
            this.render();
            return this;
        },

        /**
         * Enable pagination
         *
         * @return {*}
         */
        enable: function() {
            this.enabled = true;
            this.render();
            return this;
        },

        /**
         * jQuery event handler for the page handlers. Goes to the right page upon clicking.
         *
         * @param {Event} e
         * @protected
         */
        onChangePage: function (e) {
            e.preventDefault();

            if (!this.enabled) {
                return;
            }

            const label = $(e.target).text();
            const ffConfig = this.fastForwardHandleConfig;

            const collection = this.collection;

            if (ffConfig) {
                const prevLabel = _.has(ffConfig.prev, 'label') ? ffConfig.prev.label : undefined;
                const nextLabel = _.has(ffConfig.next, 'label') ? ffConfig.next.label : undefined;
                switch (label) {
                    case prevLabel:
                        if (collection.hasPrevious()) collection.getPreviousPage();
                        return;
                    case nextLabel:
                        if (collection.hasNext()) collection.getNextPage();
                        return;
                }
            }

            const state = collection.state;
            const pageIndex = label * 1 - state.firstPage;
            collection.getPage(state.firstPage === 0 ? pageIndex : pageIndex + 1);
        },

        /**
         * Internal method to create a list of page handle objects for the template
         * to render them.
         *
         * @return {Array.<Object>} an array of page handle objects hashes
         */
        makeHandles: function (handles) {
            handles = handles || [];

            var collection = this.collection;
            var state = collection.state;

            // convert all indices to 0-based here
            var lastPage = state.lastPage ? state.lastPage : state.firstPage;
            lastPage = state.firstPage === 0 ? lastPage : lastPage - 1;
            var currentPage = state.firstPage === 0 ? state.currentPage : state.currentPage - 1;
            var windowStart = Math.floor(currentPage / this.windowSize) * this.windowSize;
            var windowEnd = windowStart + this.windowSize;
            windowEnd = windowEnd <= lastPage ? windowEnd : lastPage + 1;

            if (collection.mode !== "infinite") {
                for (var i = windowStart; i < windowEnd; i++) {
                    handles.push({
                        label: i + 1,
                        title: "No. " + (i + 1),
                        className: currentPage === i ? "active" : undefined
                    });
                }
            }

            var ffConfig = this.fastForwardHandleConfig || {};

            if (ffConfig.prev) {
                handles.unshift({
                    label: _.has(ffConfig.prev, 'label') ? ffConfig.prev.label : undefined,
                    wrapClass: _.has(ffConfig.prev, 'wrapClass') ? ffConfig.prev.wrapClass : undefined,
                    className: collection.hasPrevious() ? undefined : 'AknActionButton--disabled'
                });
            }

            if (ffConfig.next) {
                handles.push({
                    label: _.has(ffConfig.next, 'label') ? ffConfig.next.label : undefined,
                    wrapClass: _.has(ffConfig.next, 'wrapClass') ? ffConfig.next.wrapClass : undefined,
                    className: collection.hasNext() ? void 0 : 'AknActionButton--disabled'
                });
            }

            return handles;
        },

        /**
         * Render pagination
         *
         * @return {*}
         */
        renderPagination: function() {
            this.$el.empty();

            var state = this.collection.state;

            this.$el.append($(this.template({
                disabled: !this.enabled || !state.totalRecords,
                handles: this.makeHandles(),
                state: state
            })));

            if (this.hidden) {
                this.$el.hide();
            }

            return this;
        }
    });
});
