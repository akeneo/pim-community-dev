// eslint-disable-next-line @typescript-eslint/no-var-requires
const StringCell = require('oro/datagrid/string-cell');
// eslint-disable-next-line @typescript-eslint/no-var-requires
const translate = require('oro/translator');
import ReactDOM from 'react-dom';
import React from 'react';
import styled from "styled-components";
import {TableIcon} from 'akeneo-design-system';

const Centered = styled.div`
  text-align: center;
`

class TableCell extends StringCell {
  render() {
    const value = this.model.get(this.column.get('name'));
    if (value) {
      ReactDOM.render(
        <Centered>
          <TableIcon title={translate('pim_table_attribute.datagrid.placeholder')}/>
        </Centered>,
        this.el
      );
    }
    return this;
  }
}

export = TableCell;
