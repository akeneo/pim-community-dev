import * as _ from 'underscore';

const StringCell = require('oro/datagrid/string-cell');
const CellTemplate = require('oro/datagrid/tags-cell-template');

class TagsCell extends StringCell {
  readonly template = _.template(CellTemplate);

  render() {
    const distinctActions = this.model.get('tags').actions
      .map(action => action.type)
      .filter((type, index, array) => array.indexOf(type) === index)
    ;

    this.$el.html(this.template({
      actions: distinctActions,
      getColor: this.getColor,
    }));

    return this;
  }

  getColor(action: string) {
    const colors = {
      add: 'green',
      calculate: 'darkPurple',
      clear: 'red',
      concatenate: 'purple',
      copy: 'darkBlue',
      remove: 'yellow',
      set: 'blue',
    };

    return colors[action] || 'default';
  }
}

export = TagsCell;
