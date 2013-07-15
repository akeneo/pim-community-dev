var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};

/**
 * Grid row.
 *
 * Triggers events:
 *  - "cellEdited" when one of row cell is edited
 *  - "clicked" when row is clicked
 *
 * @class   Oro.Datagrid.Row
 * @extends Backgrid.Row
 */
Oro.Datagrid.Row = Backgrid.Row.extend({

    /** @property */
    events: {
        "click": "onClick"
    },

    /** @property */
    clickData: {
        counter: 0,
        timeout: 300,
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

        if (this.clickData.counter == 0 && !this._hasSelectedText()) {
            this.clickData.counter++;
            setTimeout(_.bind(function() {
                if (!this._hasSelectedText() && this.clickData.counter == 1) {
                    this.trigger('clicked', this, e);
                }
                this.clickData.counter = 0;
            }, this), this.clickData.timeout);
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
        if (typeof window.getSelection != "undefined") {
            text = window.getSelection().toString();
        } else if (typeof document.selection != "undefined" && document.selection.type == "Text") {
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
     * Listen to events of cell, proxies events "edited" to "cellEdited"
     *
     * @param {Backgrid.Cell} cell
     * @private
     */
    _listenToCellEvents: function(cell) {
        this.listenTo(cell, 'edited', function(cell) {
            this.trigger('cellEdited', this, cell);
        });
        if (cell.listenRowClick && cell.onRowClicked && _.isFunction(cell.onRowClicked)) {
            this.on('clicked', function(e) {
                cell.onRowClicked(this, e);
            }, this);
        }
    }
});
