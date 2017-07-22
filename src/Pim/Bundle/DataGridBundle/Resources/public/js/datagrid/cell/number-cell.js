/* global define */
import _ from 'underscore';
import Backgrid from 'backgrid';


/**
 * Number column cell.
 *
 * @export  oro/datagrid/number-cell
 * @class   oro.datagrid.NumberCell
 * @extends Backgrid.NumberCell
 */
export default Backgrid.NumberCell.extend({
  /** @property {String} */
  style: 'decimal',

  /**
   * @inheritDoc
   */
  enterEditMode: function(e) {
    if (this.column.get("editable")) {
      e.stopPropagation();
    }
    return Backgrid.NumberCell.prototype.enterEditMode.apply(this, arguments);
  }
});

