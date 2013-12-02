/* global define */
define(['underscore', 'backgrid'],
function(_, Backgrid) {
    'use strict';

    /**
     * Select column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/select-cell
     * @class   oro.datagrid.SelectCell
     * @extends Backgrid.SelectCell
     */
    return Backgrid.SelectCell.extend({
        /**
         * @inheritDoc
         */
        initialize: function (options) {
            if (this.choices) {
                this.optionValues = [];
                _.each(this.choices, function(value, key) {
                    this.optionValues.push([value, key]);
                }, this);
            }
            Backgrid.SelectCell.prototype.initialize.apply(this, arguments);
        },

        /**
         * @inheritDoc
         */
        enterEditMode: function (e) {
            if (this.column.get("editable")) {
                e.stopPropagation();
            }
            return Backgrid.StringCell.prototype.enterEditMode.apply(this, arguments);
        }
    });
});
