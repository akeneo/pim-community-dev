/* eslint-disable @typescript-eslint/no-var-requires */

import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme, Tags, Tag} from 'akeneo-design-system';
const StringCell = require('oro/datagrid/string-cell');
const translate = require('oro/translator');

class TagsCell extends StringCell {
  render() {
    const distinctActionTypes = this.model
      .get('tags')
      .actions.map((action: any) => action.type)
      .filter(
        (type: string, index: number, array: string[]) =>
          array.indexOf(type) === index
      );

    ReactDOM.render(
      <ThemeProvider theme={pimTheme}>
        <Tags>
          {distinctActionTypes.map((actionType: string) => (
            <Tag tint={TagsCell.getTint(actionType)} key={actionType}>
              {translate(
                `pimee_catalog_rule.datagrid.rule-grid.tags.${actionType}`
              )}
            </Tag>
          ))}
        </Tags>
      </ThemeProvider>,
      this.el
    );

    return this;
  }

  static getTint = (actionType: string): string => {
    const tints: {[key: string]: string} = {
      add: 'green',
      calculate: 'dark_purple',
      clear: 'red',
      concatenate: 'purple',
      copy: 'dark_blue',
      remove: 'yellow',
      set: 'blue',
    };

    return tints[actionType] ?? '';
  };
}

export = TagsCell;
