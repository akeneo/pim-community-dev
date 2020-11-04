/* eslint-disable @typescript-eslint/no-var-requires */

import _ from 'underscore';

const StringCell = require('oro/datagrid/string-cell');
const CellTemplate = require('oro/datagrid/tags-cell-template');

class TagsCell extends StringCell {
  readonly template = _.template(CellTemplate);

  render() {
    const distinctActionTypes = this.model
      .get('tags')
      .actions.map((action: any) => action.type)
      .filter(
        (type: string, index: number, array: string[]) =>
          array.indexOf(type) === index
      );

    this.$el.html(
      this.template({
        getClassName: TagsCell.getClassName,
        actionTypes: distinctActionTypes,
      })
    );

    return this;
  }

  static getClassName = (actionType: string): string => {
    const colors: {[key: string]: string} = {
      add: 'green',
      calculate: 'darkPurple',
      clear: 'red',
      concatenate: 'purple',
      copy: 'darkBlue',
      remove: 'yellow',
      set: 'blue',
    };

    return colors[actionType] ? `AknTag--${colors[actionType]}` : '';
  };
}

export = TagsCell;
