const StringCell = require('oro/datagrid/string-cell');

class TagsCell extends StringCell {
  render() {
    const html = this.model.get('tags').actions
      .map(action => `<span class="AknTag AknTag--${this.getColor(action.type)}">${action.type}</span>`)
      .join('');

    this.$el.html(html);

    return this;
  }

  getColor(action: string) {
    const colors = {
      copy: 'blue',
      add: 'purple',
      set: 'red',
      calculate: 'yellow',
      concatenate: 'green',
    };

    // @TODO [EvrardCaron] Waiting for Stephane's feedback to choose which colors to use for actions
    // return colors[action] || 'default';
    return 'default'
  }
}

export = TagsCell;
