/* global define */
define(['jquery', 'underscore', 'backgrid'],
function($, _, Backgrid) {
    'use strict';

    /**
     * Boolean column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/boolean-cell
     * @class   oro.datagrid.BooleanCell
     * @extends Backgrid.BooleanCell
     */
    return Backgrid.BooleanCell.extend({
        /** @property {Boolean} */
        listenRowClick: true,

        /**
         * @inheritDoc
         */
        render: function() {
            Backgrid.BooleanCell.prototype.render.apply(this, arguments);
            this.$input = this.$el.find('input');
            if (!this.column.get('editable')) {
                this.$input.attr('disabled', 'disabled');
            }
            return this;
        },

        /**
         * @inheritDoc
         */
        enterEditMode: function(e) {
            Backgrid.BooleanCell.prototype.enterEditMode.apply(this, arguments);
            if (this.column.get('editable')) {
                var $editor = this.currentEditor.$el;
                $editor.prop('checked', !$editor.prop('checked')).change();
            }
        },

        /**
         * @param {Backgrid.Row} row
         * @param {Event} e
         */
        onRowClicked: function(row, e) {
            if (!this.$input.is(e.target) && !this.$el.is(e.target) && !this.$el.has(e.target).length){
                this.enterEditMode(e);
            }
        }
    });
});
