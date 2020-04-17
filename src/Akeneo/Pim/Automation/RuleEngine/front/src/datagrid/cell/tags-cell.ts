import * as _ from 'underscore';

const StringCell = require('oro/datagrid/string-cell');
const CellTemplate = require('oro/datagrid/tags-cell-template');

class TagsCell extends StringCell {
  readonly template = _.template(CellTemplate);

  render() {
    this.$el.html(this.template({
      actions: this.model.get('tags').actions,
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
