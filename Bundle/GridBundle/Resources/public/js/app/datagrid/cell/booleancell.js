var Oro = Oro || {};
Oro.Datagrid = Oro.Datagrid || {};
Oro.Datagrid.Cell = Oro.Datagrid.Cell || {};

/**
 * Boolean column cell. Added missing behaviour.
 *
 * Triggers events:
 *  - "edit" when a cell is entering edit mode and an editor
 *  - "editing" when a cell has finished switching to edit mode
 *  - "edited" when cell editing is finished
 *
 * @class   Oro.Datagrid.Cell.BooleanCell
 * @extends Backgrid.BooleanCell
 */
Oro.Datagrid.Cell.BooleanCell = Backgrid.BooleanCell.extend({
    /** @property {Boolean} */
    editable: false,

    /** @property {Boolean} */
    listenRowClick: true,

    /** @property {Object} */
    editor: _.template("<input type='checkbox' <%= checked ? checked='checked' : '' %> <%= editable ? '' : 'disabled' %> />'"),

    /**
     * @inheritDoc
     */
    initialize: function(options) {
        Backgrid.BooleanCell.prototype.initialize.apply(this, arguments);
        this.editable = this.column.get("editable");
    },

    /**
     * @inheritDoc
     */
    render: function () {
        this.$el.empty();
        this.currentEditor = $(this.editor({
            checked:  this.formatter.fromRaw(this.model.get(this.column.get("name"))),
            editable: this.editable
        }));
        this.$el.append(this.currentEditor);
        return this;
    },

    /**
     * @inheritDoc
     */
    enterEditMode: function (e) {
        if (this.editable) {
            Backgrid.BooleanCell.prototype.enterEditMode.apply(this, arguments);
            this.trigger("editing", this);
        }
    },

    /**
     * @inheritDoc
     */
    exitEditMode: function (e) {
        if (this.editable) {
            Backgrid.BooleanCell.prototype.exitEditMode.apply(this, arguments);
        }
    },

    /**
     * @inheritDoc
     */
    save: function (e) {
        if (this.editable) {
            Backgrid.BooleanCell.prototype.save.apply(this, arguments);
            this.trigger("edited", this);
        }
    },

    /**
     * @param {Backgrid.Row} row
     * @param {Event} e
     */
    onRowClicked: function(row, e) {
        if (this.editable && e.target !== this.currentEditor.get(0)) {
            this.currentEditor.click();
        }
    }
});
