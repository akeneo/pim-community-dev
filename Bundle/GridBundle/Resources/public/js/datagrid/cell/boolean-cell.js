/* global define */
define(['jquery', 'underscore', 'backgrid'],
function($, _, Backgrid) {
    'use strict';

    /**
     * Boolean column cell. Added missing behaviour.
     *
     * Triggers events:
     *  - "edit" when a cell is entering edit mode and an editor
     *  - "editing" when a cell has finished switching to edit mode
     *  - "edited" when cell editing is finished
     *
     * @export  oro/datagrid/boolean-cell
     * @class   oro.datagrid.BooleanCell
     * @extends Backgrid.BooleanCell
     */
    return Backgrid.BooleanCell.extend({
        /** @property {Boolean} */
        editable: false,

        /** @property {Boolean} */
        listenRowClick: true,

        /** @property {Object} */
        editor: _.template(
            "<input data-identifier='<%= dataIdentifier %>' type='checkbox' " +
            "<%= checked ? checked='checked' : '' %> <%= editable ? '' : 'disabled' %> />'"
        ),

        /** @property {String} */
        dataIdentifier: null,

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            Backgrid.BooleanCell.prototype.initialize.apply(this, arguments);
            this.editable = this.column.get("editable");
            this.dataIdentifier = this._generateUniqueIdentifier();
        },

        /**
         * @inheritDoc
         */
        render: function () {
            this.$el.empty();
            this.currentEditor = $(this.editor({
                checked:        this.formatter.fromRaw(this.model.get(this.column.get("name"))),
                editable:       this.editable,
                dataIdentifier: this.dataIdentifier
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
            if (this.editable && $(e.target).data('identifier') !== this.dataIdentifier) {
                this.currentEditor.click();
            }
        },

        /**
         * @return {String}
         */
        _generateUniqueIdentifier: function() {
            var randomString = Math.random().toString(36).slice(-8);
            return 'checkbox_' + this.cid + '_' + randomString;
        }
    });
});
