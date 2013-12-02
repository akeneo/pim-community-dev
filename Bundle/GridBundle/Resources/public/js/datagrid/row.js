/* global define */
define(['jquery', 'underscore', 'backgrid'],
function($, _, Backgrid) {
    'use strict';

    /**
     * Grid row.
     *
     * Triggers events:
     *  - "clicked" when row is clicked
     *
     * @export  oro/grid/row
     * @class   oro.grid.Row
     * @extends Backgrid.Row
     */
    return Backgrid.Row.extend({

        /** @property */
        events: {
            "click": "onClick"
        },

        /** @property */
        clickData: {
            counter: 0,
            timeout: 100,
            hasSelectedText: false
        },

        /**
         * jQuery event handler for row click, trigger "clicked" event if row element was clicked
         *
         * @param {Event} e
         */
        onClick: function(e) {
            var targetElement = e.target;
            var targetParentElement = $(e.target).parent().get(0);

            if (!this.el == targetElement && !this.el == targetParentElement) {
                return;
            }

            this.clickData.counter++;
            if (this.clickData.counter == 1 && !this._hasSelectedText()) {
                _.delay(_.bind(function() {
                    if (!this._hasSelectedText() && this.clickData.counter == 1) {
                        this.trigger('clicked', this, e);
                    }
                    this.clickData.counter = 0;
                }, this), this.clickData.timeout);
            } else {
                this.clickData.counter = 0;
            }
        },

        /**
         * Checks if selected text is available
         *
         * @returns {string}
         * @return {boolean}
         */
        _hasSelectedText: function() {
            var text = "";
            if (_.isFunction(window.getSelection)) {
                text = window.getSelection().toString();
            } else if (!_.isUndefined(document.selection) && document.selection.type == "Text") {
                text = document.selection.createRange().text;
            }
            return !_.isEmpty(text);
        },

        /**
         * @inheritDoc
         */
        makeCell: function (column) {
            var cell = new (column.get("cell"))({
                column: column,
                model: this.model
            });
            this._listenToCellEvents(cell);
            return cell;
        },

        /**
         * Listen to events of cell
         *
         * @param {Backgrid.Cell} cell
         * @private
         */
        _listenToCellEvents: function(cell) {
            if (cell.listenRowClick && _.isFunction(cell.onRowClicked)) {
                this.on('clicked', _.bind(cell.onRowClicked, cell));
            }
        }
    });
});
