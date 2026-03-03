const BaseForm = require('pim/grid/mass-actions');

class MassActions extends BaseForm {
  countEntities() {
    let count = this.count;
    if (this.datagrid) {
      const selectionState = this.datagrid.getSelectionState();
      count = Object.keys(this.datagrid.getSelectionState().selectedModels).length;
      if (!selectionState.inset) {
        count = this.collection.state.totalRecords - Object.keys(selectionState.selectedModels).length;
      }
    }

    return count;
  }
}

export = MassActions;
