/* global define */
define(['jquery', 'underscore', 'backbone', 'backgrid', 'oro/pageable-collection'],
function ($, _, Backbone, Backgrid, PageableCollection) {
    "use strict";

    /**
     * Datagrid header cell
     *
     * @export  oro/datagrid/header-cell
     * @class   oro.datagrid.HeaderCell
     * @extends Backgrid.HeaderCell
     */
    return Backgrid.HeaderCell.extend({

        /** @property */
        template:_.template(
            '<% if (sortable) { %>' +
                '<a href="#">' +
                    '<%= label %> ' +
                    '<span class="AknGrid-caret AknCaret caret"></span>' +
                '</a>' +
            '<% } else { %>' +
                '<span><%= label %></span>' + // wrap label into span otherwise underscore will not render it
            '<% } %>'
        ),

        /** @property {Boolean} */
        allowNoSorting: true,

        /**
         * Initialize.
         *
         * Add listening "reset" event of collection to able catch situation when header cell should update it's sort state.
         */
        initialize: function() {
            this.allowNoSorting = this.collection.multipleSorting;
            Backgrid.HeaderCell.prototype.initialize.apply(this, arguments);
            this._initCellDirection(this.collection);
            this.collection.on('reset', this._initCellDirection, this);
        },

        /**
         * There is no need to reset cell direction because of multiple sorting
         *
         * @private
         */
        _resetCellDirection: function () {},

        /**
         * Inits cell direction when collections loads first time.
         *
         * @param collection
         * @private
         */
        _initCellDirection: function(collection) {
            if (collection == this.collection) {
                var state = collection.state;
                var direction = null;
                var columnName = this.column.get('name');
                if (this.column.get('sortable') && _.has(state.sorters, columnName)) {
                    if (1 == state.sorters[columnName]) {
                        direction = 'descending';
                    } else if (-1 == state.sorters[columnName]) {
                        direction = 'ascending';
                    }
                }
                if (direction != this.direction()) {
                    this.direction(direction);
                }
            }
        },

        /**
         * Renders a header cell with a sorter and a label.
         *
         * @return {*}
         */
        render: function () {
            this.$el.empty();

            this.$el.append($(this.template({
                label: this.column.get("label"),
                sortable: this.column.get("sortable")
            })));

            if (this.column.has('width')) {
                this.$el.width(this.column.get('width'));
            }

            return this;
        },

        /**
         * Click on column name to perform sorting
         *
         * @param {Event} e
         */
        onClick: function (e) {
            e.preventDefault();

            var columnName = this.column.get("name");

            if (this.column.get("sortable")) {
                if (this.direction() === "ascending") {
                    this.sort(columnName, "descending", function (left, right) {
                        var leftVal = left.get(columnName);
                        var rightVal = right.get(columnName);
                        if (leftVal === rightVal) {
                            return 0;
                        }
                        else if (leftVal > rightVal) { return -1; }
                        return 1;
                    });
                }
                else if (this.allowNoSorting && this.direction() === "descending") {
                    this.sort(columnName, null);
                }
                else {
                    this.sort(columnName, "ascending", function (left, right) {
                        var leftVal = left.get(columnName);
                        var rightVal = right.get(columnName);
                        if (leftVal === rightVal) {
                            return 0;
                        }
                        else if (leftVal < rightVal) { return -1; }
                        return 1;
                    });
                }
            }
        },

        /**
         * @param {string} columnName
         * @param {null|"ascending"|"descending"} direction
         * @param {function(*, *): number} [comparator]
         */
        sort: function (columnName, direction, comparator) {
            comparator = comparator || this._cidComparator;

            var collection = this.collection;

            if (collection instanceof PageableCollection) {
                var order;
                if (direction === "ascending") order = -1;
                else if (direction === "descending") order = 1;
                else order = null;

                collection.setSorting(columnName, order);

                if (collection.mode == "client") {
                    if (!collection.fullCollection.comparator) {
                        collection.fullCollection.comparator = comparator;
                    }
                    collection.fullCollection.sort();
                }
                else collection.fetch();
            }
            else {
                collection.comparator = comparator;
                collection.sort();
            }

            /**
             * Global Backbone event. Fired when the sorter is clicked on a sortable column.
             */
            Backbone.trigger("backgrid:sort", columnName, direction, comparator, this.collection);
        }
    });
});
