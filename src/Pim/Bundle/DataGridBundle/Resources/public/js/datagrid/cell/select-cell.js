/* global define */
import _ from 'underscore';
import Backgrid from 'backgrid';
    

    /**
     * Select column cell. Added missing behaviour.
     *
     * @export  oro/datagrid/select-cell
     * @class   oro.datagrid.SelectCell
     * @extends Backgrid.SelectCell
     */
    export default Backgrid.SelectCell.extend({
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

